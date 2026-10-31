<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Display directory of all employees.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search', '');
        $department = $request->query('department', '');
        $status = $request->query('status', '');
        $contractType = $request->query('contract_type', '');

        $query = Employee::with(['user.roles'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('biometric_id', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            })
            ->when($department, fn($q) => $q->where('department', $department))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($contractType, fn($q) => $q->where('contract_type', $contractType));

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'Activo')->count();
        $inactiveEmployees = Employee::where('status', 'Inactivo')->count();
        $monthlyPayrollEstimate = Employee::where('status', 'Activo')
            ->where('contract_type', 'Mensual')
            ->sum('base_salary');

        $employees = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $departments = Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        $usersAvailable = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['Estudiante', 'Solicitante']);
        })->whereDoesntHave('employee')->orderBy('name')->get(['id', 'name', 'email']);

        $roles = Role::whereNotIn('name', ['Estudiante', 'Solicitante'])->orderBy('name')->pluck('name');

        return Inertia::render('Hr/Employees/Index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'department' => $department,
                'status' => $status,
                'contract_type' => $contractType,
            ],
            'stats' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees,
                'inactive' => $inactiveEmployees,
                'monthly_payroll_estimate' => (float)$monthlyPayrollEstimate,
            ],
            'departments' => $departments,
            'usersAvailable' => $usersAvailable,
            'roles' => $roles,
        ]);
    }

    /**
     * Store new employee record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'is_new_user' => 'required|boolean',
            'user_id' => 'nullable|required_if:is_new_user,false|exists:users,id',
            'new_user_name' => 'nullable|required_if:is_new_user,true|string|max:255',
            'new_user_email' => 'nullable|required_if:is_new_user,true|email|unique:users,email',
            'new_user_password' => 'nullable|required_if:is_new_user,true|min:8',
            'role_name' => 'required|exists:roles,name',
            'biometric_id' => 'nullable|integer|unique:employees,biometric_id',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'contract_type' => 'required|in:Mensual,Por Horas',
            'base_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:Activo,Inactivo',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['is_new_user']) {
                $user = User::create([
                    'name' => $validated['new_user_name'],
                    'email' => $validated['new_user_email'],
                    'password' => Hash::make($validated['new_user_password']),
                ]);
                $userId = $user->id;
            } else {
                $userId = $validated['user_id'];
                $user = User::findOrFail($userId);
            }

            $user->syncRoles([$validated['role_name']]);

            $employee = Employee::create([
                'user_id' => $userId,
                'biometric_id' => $validated['biometric_id'] ?? null,
                'position' => $validated['position'] ?? '',
                'department' => $validated['department'] ?? '',
                'contract_type' => $validated['contract_type'],
                'base_salary' => $validated['base_salary'] ?? 0,
                'hourly_rate' => $validated['hourly_rate'] ?? 0,
                'hire_date' => $validated['hire_date'] ?? null,
                'status' => $validated['status'],
            ]);

            DB::commit();
            return back()->with('success', "Empleado {$user->name} creado exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar empleado: ' . $e->getMessage());
        }
    }

    /**
     * Update employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'role_name' => 'required|exists:roles,name',
            'biometric_id' => 'nullable|integer|unique:employees,biometric_id,' . $employee->id,
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'contract_type' => 'required|in:Mensual,Por Horas',
            'base_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'required|in:Activo,Inactivo',
        ]);

        DB::beginTransaction();
        try {
            $employee->user->syncRoles([$validated['role_name']]);

            $employee->update([
                'biometric_id' => $validated['biometric_id'] ?? null,
                'position' => $validated['position'] ?? '',
                'department' => $validated['department'] ?? '',
                'contract_type' => $validated['contract_type'],
                'base_salary' => $validated['base_salary'] ?? 0,
                'hourly_rate' => $validated['hourly_rate'] ?? 0,
                'hire_date' => $validated['hire_date'] ?? null,
                'status' => $validated['status'],
            ]);

            DB::commit();
            return back()->with('success', 'Expediente del empleado actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar expediente: ' . $e->getMessage());
        }
    }

    /**
     * Display full dossier/profile for an employee.
     */
    public function profile(Employee $employee): Response
    {
        $employee->load([
            'user.roles',
            'attendances' => fn($q) => $q->latest('punch_time')->take(30),
            'payrollItems.payroll',
        ]);

        $events = class_exists(EmployeeEvent::class)
            ? EmployeeEvent::where('employee_id', $employee->id)->orderBy('event_date', 'desc')->get()
            : collect();

        $roles = Role::whereNotIn('name', ['Estudiante', 'Solicitante'])->orderBy('name')->pluck('name');

        return Inertia::render('Hr/Employees/Profile', [
            'employee' => [
                'id' => $employee->id,
                'user_id' => $employee->user_id,
                'name' => $employee->user?->name ?? 'Empleado',
                'email' => $employee->user?->email ?? '',
                'role' => $employee->user?->roles->first()?->name ?? 'Sin Rol',
                'biometric_id' => $employee->biometric_id,
                'position' => $employee->position,
                'department' => $employee->department,
                'contract_type' => $employee->contract_type,
                'base_salary' => (float)$employee->base_salary,
                'hourly_rate' => (float)$employee->hourly_rate,
                'hire_date' => $employee->hire_date?->format('d/m/Y'),
                'hire_date_raw' => $employee->hire_date?->format('Y-m-d'),
                'status' => $employee->status,
                'created_at' => $employee->created_at?->format('d/m/Y'),
            ],
            'attendances' => $employee->attendances->map(fn($a) => [
                'id' => $a->id,
                'punch_time' => $a->punch_time?->format('d/m/Y H:i:s'),
                'punch_type' => $a->punch_type,
                'punch_type_label' => $a->punch_type == 0 ? 'Entrada' : 'Salida',
                'device_ip' => $a->device_ip ?? 'Biométrico #1',
            ]),
            'payrollHistory' => $employee->payrollItems->map(fn($pi) => [
                'id' => $pi->id,
                'payroll_name' => $pi->payroll?->name ?? 'Nómina',
                'period' => ($pi->payroll?->start_date?->format('d/m/Y') ?? '') . ' - ' . ($pi->payroll?->end_date?->format('d/m/Y') ?? ''),
                'base_amount' => (float)$pi->base_amount,
                'deductions' => (float)$pi->deductions,
                'net_amount' => (float)$pi->net_amount,
                'status' => $pi->payroll?->status ?? 'Borrador',
                'date' => $pi->created_at?->format('d/m/Y'),
            ]),
            'events' => $events->map(fn($ev) => [
                'id' => $ev->id,
                'type' => $ev->type,
                'event_date' => $ev->event_date?->format('d/m/Y'),
                'end_date' => $ev->end_date?->format('d/m/Y'),
                'amount' => (float)$ev->amount,
                'score' => $ev->score,
                'description' => $ev->description,
            ]),
            'roles' => $roles,
        ]);
    }

    /**
     * Update salary parameters.
     */
    public function updateSalary(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'contract_type' => 'required|in:Mensual,Por Horas',
            'base_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $employee->update([
            'contract_type' => $validated['contract_type'],
            'base_salary' => $validated['base_salary'] ?? 0,
            'hourly_rate' => $validated['hourly_rate'] ?? 0,
        ]);

        return back()->with('success', 'Parámetros salariales actualizados.');
    }

    /**
     * Update role.
     */
    public function updateRole(Request $request, Employee $employee)
    {
        $request->validate(['role_name' => 'required|exists:roles,name']);
        $employee->user->syncRoles([$request->input('role_name')]);
        return back()->with('success', 'Rol del sistema asignado correctamente.');
    }

    /**
     * Update biometric ID.
     */
    public function updateBiometric(Request $request, Employee $employee)
    {
        $request->validate([
            'biometric_id' => 'nullable|integer|unique:employees,biometric_id,' . $employee->id,
        ]);

        $employee->update(['biometric_id' => $request->input('biometric_id')]);
        return back()->with('success', 'ID biométrico sincronizado.');
    }

    /**
     * Store employee event / incident.
     */
    public function storeEvent(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'event_type' => 'required|string',
            'event_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_amount' => 'nullable|numeric',
            'event_score' => 'nullable|integer|min:1|max:5',
            'event_description' => 'required|string|max:1000',
        ]);

        EmployeeEvent::create([
            'employee_id' => $employee->id,
            'type' => $validated['event_type'],
            'event_date' => $validated['event_date'],
            'end_date' => $validated['event_end_date'] ?? null,
            'amount' => $validated['event_amount'] ?? null,
            'score' => $validated['event_score'] ?? null,
            'description' => $validated['event_description'],
        ]);

        return back()->with('success', 'Evento registrado en el expediente del empleado.');
    }

    /**
     * Regenerate Kiosk PIN.
     */
    public function regeneratePin(Employee $employee)
    {
        $pin = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $employee->user->update(['kiosk_pin' => Hash::make($pin)]);
        return back()->with('success', "NUEVO PIN GENERADO: {$pin}. Proporciónelo al colaborador.");
    }

    /**
     * Ban / Deactivate employee.
     */
    public function banUser(Employee $employee)
    {
        $employee->user->update(['access_expires_at' => now()->subDay()]);
        $employee->update(['status' => 'Inactivo']);
        return back()->with('success', 'Acceso revocado y empleado desactivado.');
    }
}
