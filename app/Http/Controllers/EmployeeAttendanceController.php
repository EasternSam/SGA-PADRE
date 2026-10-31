<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeAttendanceController extends Controller
{
    /**
     * Muestra el registro biométrico y reloj de personal institucional.
     */
    public function index(Request $request)
    {
        $date = $request->input('date') ?: Carbon::today()->format('Y-m-d');
        $search = trim($request->input('search', ''));

        $query = EmployeeAttendance::with(['employee.user'])
            ->whereDate('punch_time', $date);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('biometric_id', 'like', "%{$search}%")
                  ->orWhereHas('employee.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $attendances = $query->orderBy('punch_time', 'desc')->paginate(20)->withQueryString();

        // Estadísticas del día
        $dayQuery = EmployeeAttendance::whereDate('punch_time', $date);
        $totalPunches = (clone $dayQuery)->count();
        $uniqueEmployees = (clone $dayQuery)->distinct('biometric_id')->count('biometric_id');
        $entriesCount = (clone $dayQuery)->whereIn('punch_type', ['check_in', 'Entrada'])->count();
        $exitsCount = (clone $dayQuery)->whereIn('punch_type', ['check_out', 'Salida'])->count();

        $stats = [
            'total_punches' => $totalPunches,
            'unique_employees' => $uniqueEmployees,
            'entries_count' => $entriesCount,
            'exits_count' => $exitsCount,
        ];

        $employees = Employee::with('user')->orderBy('position')->get()->map(fn($e) => [
            'id' => $e->id,
            'biometric_id' => $e->biometric_id,
            'name' => $e->user?->name ?? 'Empleado',
            'position' => $e->position,
            'department' => $e->department,
        ]);

        return Inertia::render('Hr/AttendanceClock', [
            'attendances' => $attendances,
            'employees' => $employees,
            'selectedDate' => $date,
            'filters' => [
                'date' => $date,
                'search' => $search,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Registra manualmente un ponche de personal.
     */
    public function storePunch(Request $request)
    {
        $validated = $request->validate([
            'biometric_id' => 'required|string|max:50',
            'punch_time' => 'required|date',
            'punch_type' => 'required|in:check_in,check_out,Entrada,Salida',
            'device_serial' => 'nullable|string|max:100',
        ], [
            'biometric_id.required' => 'Debe indicar el ID biométrico del empleado.',
            'punch_time.required' => 'La fecha y hora del ponche es obligatoria.',
        ]);

        EmployeeAttendance::create([
            'biometric_id' => $validated['biometric_id'],
            'punch_time' => $validated['punch_time'],
            'punch_type' => $validated['punch_type'],
            'device_serial' => $validated['device_serial'] ?? 'MANUAL-ADMIN',
        ]);

        return redirect()->back()->with('success', 'Ponche registrado correctamente en el reloj.');
    }

    /**
     * Elimina un ponche erróneo.
     */
    public function destroyPunch(EmployeeAttendance $attendance)
    {
        $attendance->delete();
        return redirect()->back()->with('success', 'Registro de ponche eliminado.');
    }
}
