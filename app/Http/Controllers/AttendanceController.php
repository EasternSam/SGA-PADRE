<?php

namespace App\Http\Controllers;

use App\Models\AbsenceJustification;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    /**
     * Muestra la interfaz de pase de lista diario escolar por sección.
     */
    public function register(Request $request)
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $sections = $activeYear
            ? Section::where('academic_year_id', $activeYear->id)
                ->with('gradeLevel')
                ->orderBy('grade_level_id')
                ->orderBy('name')
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->full_name ?: ($s->gradeLevel?->short_name . ' ' . $s->name),
                    'grade_level' => $s->gradeLevel?->name,
                ])
            : collect();

        $sectionId = $request->input('section_id');
        if (!$sectionId && $sections->isNotEmpty()) {
            $sectionId = (string) $sections->first()['id'];
        }

        $date = $request->input('date') ?: now()->format('Y-m-d');

        $attendances = [];
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'permission' => 0];
        $hasExisting = false;

        if ($sectionId && $date) {
            $students = Student::where('section_id', $sectionId)
                ->where('status', 'Activo')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            // Si la sección no tiene estudiantes asignados directamente por columna section_id,
            // podemos buscar estudiantes activos generales o por grado
            if ($students->isEmpty()) {
                $students = Student::where('status', 'Activo')
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->take(30)
                    ->get();
            }

            $existing = StudentAttendance::where('section_id', $sectionId)
                ->whereDate('date', $date)
                ->pluck('status', 'student_id');

            $hasExisting = $existing->isNotEmpty();

            $attendances = $students->map(function ($s) use ($existing, &$summary) {
                $status = $existing[$s->id] ?? 'present';
                if (isset($summary[$status])) {
                    $summary[$status]++;
                }
                return [
                    'student_id'   => $s->id,
                    'student_name' => $s->full_name ?: "{$s->first_name} {$s->last_name}",
                    'student_code' => $s->student_code ?? "EST-{$s->id}",
                    'cedula'       => $s->cedula,
                    'status'       => $status,
                ];
            })->toArray();
        }

        return Inertia::render('Attendance/Register', [
            'activeYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
            ] : null,
            'sections' => $sections,
            'selectedSectionId' => $sectionId ? (int) $sectionId : null,
            'selectedDate' => $date,
            'attendances' => $attendances,
            'summary' => $summary,
            'hasExisting' => $hasExisting,
            'statusLabels' => StudentAttendance::STATUS_LABELS,
        ]);
    }

    /**
     * Guarda el registro de asistencia del día para la sección.
     */
    public function saveRegister(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused,permission',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            return redirect()->back()->withErrors(['general' => 'No hay un año escolar activo para registrar asistencia.']);
        }

        DB::transaction(function () use ($validated, $activeYear) {
            foreach ($validated['attendances'] as $item) {
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'date'       => $validated['date'],
                    ],
                    [
                        'section_id'       => $validated['section_id'],
                        'academic_year_id' => $activeYear->id,
                        'status'           => $item['status'],
                        'recorded_by'      => Auth::id() ?? 1,
                    ]
                );
            }
        });

        $formattedDate = Carbon::parse($validated['date'])->format('d/m/Y');
        return redirect()->back()->with('success', "Asistencia guardada correctamente para el día {$formattedDate}.");
    }

    /**
     * Muestra el panel de gestión de excusas y justificaciones de inasistencia.
     */
    public function justifications(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim($request->input('search', ''));

        $query = AbsenceJustification::with(['student.user', 'reviewer']);

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        $justifications = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'total' => AbsenceJustification::count(),
            'pending' => AbsenceJustification::where('status', 'pending')->count(),
            'approved' => AbsenceJustification::where('status', 'approved')->count(),
            'rejected' => AbsenceJustification::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Attendance/Justifications', [
            'justifications' => $justifications,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'stats' => $stats,
            'reasons' => AbsenceJustification::REASONS,
            'statuses' => AbsenceJustification::STATUSES,
        ]);
    }

    /**
     * Registra una nueva justificación de inasistencia.
     */
    public function storeJustification(Request $request)
    {
        $validated = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'reason'      => 'required|string',
            'description' => 'nullable|string',
            'document'    => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ], [
            'student_id.required' => 'Debe seleccionar un estudiante.',
            'date_to.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $docPath = null;
        if ($request->hasFile('document')) {
            $docPath = $request->file('document')->store('justifications', 'public');
        }

        AbsenceJustification::create([
            'student_id'    => $validated['student_id'],
            'date_from'     => $validated['date_from'],
            'date_to'       => $validated['date_to'],
            'reason'        => $validated['reason'],
            'description'   => $validated['description'] ?? null,
            'document_path' => $docPath,
            'status'        => 'pending',
            'submitted_by'  => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Justificación de inasistencia registrada.');
    }

    /**
     * Aprueba la justificación y actualiza las ausencias del rango a 'excused'.
     */
    public function approveJustification(AbsenceJustification $justification)
    {
        DB::transaction(function () use ($justification) {
            $justification->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id() ?? 1,
            ]);

            // Sincronizar asistencias del estudiante
            StudentAttendance::where('student_id', $justification->student_id)
                ->whereBetween('date', [$justification->date_from, $justification->date_to])
                ->where('status', 'absent')
                ->update(['status' => 'excused']);
        });

        return redirect()->back()->with('success', 'Justificación aprobada y ausencias actualizadas a excusadas.');
    }

    /**
     * Rechaza la justificación.
     */
    public function rejectJustification(AbsenceJustification $justification)
    {
        $justification->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Justificación rechazada.');
    }

    /**
     * Elimina una justificación.
     */
    public function destroyJustification(AbsenceJustification $justification)
    {
        $justification->delete();
        return redirect()->back()->with('success', 'Justificación eliminada.');
    }
}
