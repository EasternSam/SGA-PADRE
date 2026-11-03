<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CareerController extends Controller
{
    /**
     * Directorio de Carreras Universitarias / Grados.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = Course::where('program_type', 'degree')
            ->withCount(['modules', 'schedules', 'students']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('degree_title', 'like', "%{$search}%");
            });
        }

        $careers = $query->orderBy('name')->paginate(12)->withQueryString();

        $metrics = [
            'total_careers' => Course::where('program_type', 'degree')->count(),
            'active_careers' => Course::where('program_type', 'degree')->where('status', 'Activo')->count(),
            'total_curriculum_subjects' => Module::whereHas('course', fn($q) => $q->where('program_type', 'degree'))->count(),
        ];

        return Inertia::render('Academic/Careers/Index', [
            'careers' => $careers,
            'filters' => [
                'search' => $search,
            ],
            'metrics' => $metrics,
        ]);
    }

    /**
     * Crear nueva carrera universitaria.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses,code',
            'degree_title' => 'required|string|max:255',
            'total_credits' => 'required|integer|min:0',
            'duration_periods' => 'required|integer|min:1|max:20',
            'registration_fee' => 'required|numeric|min:0',
            'monthly_fee' => 'required|numeric|min:0',
            'credit_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_sequential' => 'boolean',
        ]);

        $career = Course::create([
            'program_type' => 'degree',
            'name' => $validated['name'],
            'code' => $validated['code'],
            'degree_title' => $validated['degree_title'],
            'total_credits' => $validated['total_credits'],
            'duration_periods' => $validated['duration_periods'],
            'registration_fee' => $validated['registration_fee'],
            'monthly_fee' => $validated['monthly_fee'],
            'credit_price' => $validated['credit_price'] ?? 0,
            'description' => $validated['description'] ?? null,
            'is_sequential' => (bool)($validated['is_sequential'] ?? true),
            'status' => 'Activo',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Creación de Carrera',
            'description' => "Se creó la carrera universitaria {$career->name} ({$career->code})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Carrera universitaria creada exitosamente.');
    }

    /**
     * Actualizar carrera universitaria.
     */
    public function update(Request $request, Course $career)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('courses', 'code')->ignore($career->id)],
            'degree_title' => 'required|string|max:255',
            'total_credits' => 'required|integer|min:0',
            'duration_periods' => 'required|integer|min:1|max:20',
            'registration_fee' => 'required|numeric|min:0',
            'monthly_fee' => 'required|numeric|min:0',
            'credit_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_sequential' => 'boolean',
            'status' => 'required|in:Activo,Inactivo',
        ]);

        $career->update($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Actualización de Carrera',
            'description' => "Se actualizaron los datos de la carrera {$career->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Carrera universitaria actualizada exitosamente.');
    }

    /**
     * Eliminar carrera y su malla curricular.
     */
    public function destroy(Request $request, Course $career)
    {
        if ($career->enrollments()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar la carrera porque existen alumnos inscritos en ella.'
            ]);
        }

        try {
            DB::transaction(function () use ($career, $request) {
                $name = $career->name;
                foreach ($career->modules as $subject) {
                    $subject->prerequisites()->detach();
                    $subject->requiredFor()->detach();
                    $subject->schedules()->delete();
                    $subject->delete();
                }
                $career->delete();

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Eliminación de Carrera',
                    'description' => "Se eliminó la carrera {$name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->route('admin.careers.index')->with('success', 'Carrera y su plan de estudio eliminados correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar carrera: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al eliminar carrera: ' . $e->getMessage()]);
        }
    }

    /**
     * Malla Curricular Interactiva del Pensum Académico (Curriculum Tree).
     */
    public function curriculum(Course $career)
    {
        if ($career->program_type !== 'degree') {
            abort(404, 'El programa seleccionado no es una carrera de grado.');
        }

        $allSubjects = $career->modules()
            ->with(['prerequisites:id,name,code', 'requiredFor:id,name,code'])
            ->orderBy('period_number')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Agrupar materias por cuatrimestre / período
        $periods = [];
        $maxPeriod = max($career->duration_periods ?: 8, (int) $allSubjects->max('period_number'));

        for ($p = 1; $p <= $maxPeriod; $p++) {
            $subjectsInPeriod = $allSubjects->where('period_number', $p)->values();
            $periodCredits = $subjectsInPeriod->sum('credits');
            $periods[] = [
                'period_number' => $p,
                'period_label' => "Cuatrimestre {$p}",
                'total_credits' => $periodCredits,
                'subjects' => $subjectsInPeriod,
            ];
        }

        $totalCurriculumCredits = $allSubjects->sum('credits');

        return Inertia::render('Academic/Careers/CurriculumTree', [
            'career' => $career,
            'periods' => $periods,
            'allSubjects' => $allSubjects->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'credits' => $s->credits,
                'period_number' => $s->period_number,
                'moodle_course_id' => $s->moodle_course_id,
            ]),
            'metrics' => [
                'total_subjects' => $allSubjects->count(),
                'total_credits' => $totalCurriculumCredits,
                'duration_periods' => $career->duration_periods,
            ],
        ]);
    }

    /**
     * Crear asignatura dentro de la malla curricular.
     */
    public function storeSubject(Request $request, Course $career)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'credits' => 'required|integer|min:0|max:20',
            'period_number' => 'required|integer|min:1|max:25',
            'is_elective' => 'boolean',
            'description' => 'nullable|string',
            'prerequisite_ids' => 'nullable|array',
            'prerequisite_ids.*' => 'exists:modules,id',
        ]);

        $subject = Module::create([
            'course_id' => $career->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'credits' => $validated['credits'],
            'period_number' => $validated['period_number'],
            'is_elective' => (bool)($validated['is_elective'] ?? false),
            'description' => $validated['description'] ?? null,
            'status' => 'Activo',
        ]);

        if (!empty($validated['prerequisite_ids'])) {
            $subject->prerequisites()->sync($validated['prerequisite_ids']);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Creación de Asignatura Pensum',
            'description' => "Se añadió la materia {$subject->name} a la carrera {$career->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', "Asignatura {$subject->name} agregada al pensum exitosamente.");
    }

    /**
     * Actualizar asignatura y sus prerrequisitos.
     */
    public function updateSubject(Request $request, Course $career, Module $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
            'credits' => 'required|integer|min:0|max:20',
            'period_number' => 'required|integer|min:1|max:25',
            'is_elective' => 'boolean',
            'description' => 'nullable|string',
            'prerequisite_ids' => 'nullable|array',
            'prerequisite_ids.*' => 'exists:modules,id',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'credits' => $validated['credits'],
            'period_number' => $validated['period_number'],
            'is_elective' => (bool)($validated['is_elective'] ?? false),
            'description' => $validated['description'] ?? null,
        ]);

        $subject->prerequisites()->sync($validated['prerequisite_ids'] ?? []);

        return redirect()->back()->with('success', "Materia {$subject->name} actualizada correctamente.");
    }

    /**
     * Eliminar asignatura de la malla.
     */
    public function destroySubject(Request $request, Course $career, Module $subject)
    {
        if ($subject->schedules()->has('enrollments')->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede remover esta materia porque tiene estudiantes inscritos en secciones activas.'
            ]);
        }

        $subject->prerequisites()->detach();
        $subject->requiredFor()->detach();
        $subject->schedules()->delete();
        $subject->delete();

        return redirect()->back()->with('success', 'Asignatura eliminada del pensum.');
    }

    /**
     * Enlazar o desenlazar una materia del pensum con Moodle.
     */
    public function linkSubjectMoodle(Request $request, Course $career, Module $subject)
    {
        $validated = $request->validate([
            'moodle_course_id' => 'nullable|integer',
        ]);

        $moodleCourseId = $validated['moodle_course_id'] ?: null;
        $subject->moodle_course_id = $moodleCourseId;
        $subject->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Vinculación Pensum Moodle',
            'description' => "Se actualizó el enlace Moodle de la materia {$subject->name} ({$subject->code}) a ID: " . ($moodleCourseId ?: 'Desvinculado'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $msg = $moodleCourseId 
            ? "Materia '{$subject->name}' enlazada con éxito a Moodle ID: {$moodleCourseId}."
            : "Enlace Moodle de la materia '{$subject->name}' eliminado.";

        return redirect()->back()->with('success', $msg);
    }
}
