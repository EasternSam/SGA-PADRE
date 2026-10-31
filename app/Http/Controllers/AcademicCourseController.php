<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseMapping;
use App\Models\CourseSchedule;
use App\Models\Module;
use App\Models\ScheduleMapping;
use App\Models\User;
use App\Services\MoodleApiService;
use App\Services\WordpressApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AcademicCourseController extends Controller
{
    /**
     * Oferta Académica de Cursos Técnicos, Módulos y Secciones.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $selectedCourseId = $request->input('selectedCourse');
        $selectedModuleId = $request->input('selectedModule');

        // 1. Cursos Técnicos e Instituto (no de tipo 'degree')
        $coursesQuery = Course::where(function ($q) {
            $q->where('program_type', '!=', 'degree')
              ->orWhereNull('program_type');
        });

        if (!empty($search)) {
            $coursesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $courses = $coursesQuery
            ->with(['mapping'])
            ->withCount('modules')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 2. Módulos del curso seleccionado
        $modules = [];
        $selectedCourse = null;
        if ($selectedCourseId) {
            $selectedCourse = Course::with('mapping')->find($selectedCourseId);
            if ($selectedCourse) {
                $modules = Module::where('course_id', $selectedCourseId)
                    ->withCount('schedules')
                    ->orderBy('order')
                    ->orderBy('name')
                    ->get();
            }
        }

        // 3. Secciones del módulo seleccionado
        $schedules = [];
        $selectedModule = null;
        if ($selectedModuleId) {
            $selectedModule = Module::with(['course.mapping'])->find($selectedModuleId);
            if ($selectedModule) {
                $schedules = CourseSchedule::where('module_id', $selectedModuleId)
                    ->with([
                        'teacher:id,name,email',
                        'classroom:id,name',
                        'mapping',
                        'enrollments' => fn($q) => $q->whereIn('status', ['Activo', 'Cursando', 'Pendiente']),
                    ])
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($sch) {
                        return [
                            'id' => $sch->id,
                            'section_name' => $sch->section_name ?: "Sección #{$sch->id}",
                            'teacher_id' => $sch->teacher_id,
                            'teacher_name' => $sch->teacher?->name ?? 'Sin asignar',
                            'classroom_id' => $sch->classroom_id,
                            'classroom_name' => $sch->classroom?->name ?? 'Virtual / Sin aula',
                            'days' => $sch->days_of_week ?? [],
                            'start_time' => $sch->start_time ? Carbon::parse($sch->start_time)->format('h:i A') : '',
                            'end_time' => $sch->end_time ? Carbon::parse($sch->end_time)->format('h:i A') : '',
                            'raw_start_time' => $sch->start_time ? Carbon::parse($sch->start_time)->format('H:i') : '',
                            'raw_end_time' => $sch->end_time ? Carbon::parse($sch->end_time)->format('H:i') : '',
                            'start_date' => $sch->start_date ? Carbon::parse($sch->start_date)->format('Y-m-d') : '',
                            'end_date' => $sch->end_date ? Carbon::parse($sch->end_date)->format('Y-m-d') : '',
                            'capacity' => $sch->capacity ?? 30,
                            'enrolled_count' => $sch->enrollments->count(),
                            'modality' => $sch->modality ?? 'Presencial',
                            'status' => $sch->status ?? 'Activo',
                            'moodle_course_id' => $sch->moodle_course_id,
                            'mapping' => $sch->mapping ? [
                                'id' => $sch->mapping->id,
                                'wp_schedule_string' => $sch->mapping->wp_schedule_string,
                                'wp_course_id' => $sch->mapping->wp_course_id,
                            ] : null,
                        ];
                    });
            }
        }

        $teachers = User::role('Profesor')->select('id', 'name')->orderBy('name')->get();
        $classrooms = Classroom::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Academic/Courses/Index', [
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'modules' => $modules,
            'selectedModule' => $selectedModule,
            'schedules' => $schedules,
            'teachers' => $teachers,
            'classrooms' => $classrooms,
            'filters' => [
                'search' => $search,
                'selectedCourse' => $selectedCourseId,
                'selectedModule' => $selectedModuleId,
            ],
            'metrics' => [
                'total_courses' => Course::where(fn($q) => $q->where('program_type', '!=', 'degree')->orWhereNull('program_type'))->count(),
                'total_modules' => Module::count(),
                'total_schedules' => CourseSchedule::count(),
            ],
        ]);
    }

    /**
     * Crear un nuevo curso técnico.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses,code',
            'registration_fee' => 'required|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'is_sequential' => 'boolean',
        ]);

        $course = Course::create([
            'program_type' => 'course',
            'name' => $validated['name'],
            'code' => $validated['code'],
            'registration_fee' => $validated['registration_fee'],
            'monthly_fee' => $validated['monthly_fee'] ?? 0,
            'is_sequential' => (bool)($validated['is_sequential'] ?? false),
            'status' => 'Activo',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Creación de Curso',
            'description' => "Se creó el curso técnico {$course->name} ({$course->code})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Curso técnico creado exitosamente.');
    }

    /**
     * Actualizar curso técnico.
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('courses', 'code')->ignore($course->id)],
            'registration_fee' => 'required|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'is_sequential' => 'boolean',
            'status' => 'nullable|string|in:Activo,Inactivo',
        ]);

        $course->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'registration_fee' => $validated['registration_fee'],
            'monthly_fee' => $validated['monthly_fee'] ?? $course->monthly_fee,
            'is_sequential' => (bool)($validated['is_sequential'] ?? false),
            'status' => $validated['status'] ?? $course->status,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Actualización de Curso',
            'description' => "Se actualizó el curso {$course->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Curso técnico actualizado correctamente.');
    }

    /**
     * Eliminar curso técnico de forma segura.
     */
    public function destroy(Request $request, Course $course)
    {
        $hasEnrollments = $course->enrollments()->exists();
        if ($hasEnrollments) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar el curso porque posee alumnos inscritos.'
            ]);
        }

        try {
            DB::transaction(function () use ($course, $request) {
                $name = $course->name;
                foreach ($course->modules as $mod) {
                    $mod->schedules()->delete();
                    $mod->delete();
                }
                if ($course->mapping) {
                    $course->mapping->delete();
                }
                $course->delete();

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Eliminación de Curso',
                    'description' => "Se eliminó el curso técnico {$name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->route('admin.courses.index')->with('success', 'Curso eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar curso: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al eliminar el curso: ' . $e->getMessage()]);
        }
    }

    /**
     * Crear módulo para un curso técnico.
     */
    public function storeModule(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'price' => $validated['price'] ?? 0,
            'duration_hours' => $validated['duration_hours'] ?? null,
            'order' => $validated['order'] ?? 0,
            'status' => 'Activo',
        ]);

        return redirect()->back()->with('success', "Módulo {$module->name} creado correctamente.");
    }

    /**
     * Actualizar módulo.
     */
    public function updateModule(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ]);

        $module->update($validated);

        return redirect()->back()->with('success', "Módulo {$module->name} actualizado correctamente.");
    }

    /**
     * Eliminar módulo.
     */
    public function destroyModule(Request $request, Module $module)
    {
        if ($module->schedules()->has('enrollments')->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar el módulo porque tiene secciones con estudiantes inscritos.'
            ]);
        }

        $module->schedules()->delete();
        $module->delete();

        return redirect()->back()->with('success', 'Módulo eliminado exitosamente.');
    }

    /**
     * Crear sección / horario para un módulo.
     */
    public function storeSchedule(Request $request, Module $module)
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:100',
            'teacher_id' => 'nullable|exists:users,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'nullable|integer|min:1',
            'modality' => 'nullable|string|max:50',
        ]);

        CourseSchedule::create([
            'module_id' => $module->id,
            'teacher_id' => $validated['teacher_id'] ?? null,
            'classroom_id' => $validated['classroom_id'] ?? null,
            'section_name' => $validated['section_name'],
            'days_of_week' => $validated['days_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'capacity' => $validated['capacity'] ?? 30,
            'modality' => $validated['modality'] ?? 'Presencial',
            'status' => 'Activo',
        ]);

        return redirect()->back()->with('success', 'Sección académica creada exitosamente.');
    }

    /**
     * Actualizar sección / horario.
     */
    public function updateSchedule(Request $request, CourseSchedule $schedule)
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:100',
            'teacher_id' => 'nullable|exists:users,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'nullable|integer|min:1',
            'modality' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:Activo,Inactivo',
        ]);

        $schedule->update($validated);

        return redirect()->back()->with('success', 'Sección actualizada correctamente.');
    }

    /**
     * Eliminar sección.
     */
    public function destroySchedule(Request $request, CourseSchedule $schedule)
    {
        if ($schedule->enrollments()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar la sección porque tiene estudiantes inscritos.'
            ]);
        }

        if ($schedule->mapping) {
            $schedule->mapping->delete();
        }

        $schedule->delete();

        return redirect()->back()->with('success', 'Sección eliminada exitosamente.');
    }

    // =========================================================================
    // FUNCIONES DE INTEGRACIÓN CON WORDPRESS & MOODLE
    // =========================================================================

    /**
     * Obtener lista de cursos remotos de WordPress (Academic+ Catalog).
     */
    public function getWpCourses(WordpressApiService $wpService)
    {
        try {
            $courses = $wpService->getSgaCourses();
            if (empty($courses) && ($error = $wpService->getLastError())) {
                return response()->json([
                    'success' => false,
                    'message' => $error,
                    'data' => [],
                ], 422);
            }
            return response()->json([
                'success' => true,
                'data' => $courses,
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicCourseController@getWpCourses Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con WordPress: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Vincular curso con WordPress (CourseMapping).
     */
    public function linkWpCourse(Request $request, Course $course, WordpressApiService $wpService)
    {
        $validated = $request->validate([
            'wp_course_id' => 'nullable|integer',
        ]);

        $wpCourseId = $validated['wp_course_id'] ?? null;

        if (empty($wpCourseId)) {
            // Desvincular
            if ($course->mapping) {
                // Eliminar mapeos de horarios de secciones asociadas
                $moduleIds = $course->modules()->pluck('id');
                $scheduleIds = CourseSchedule::whereIn('module_id', $moduleIds)->pluck('id');
                ScheduleMapping::whereIn('course_schedule_id', $scheduleIds)->delete();
                $course->mapping->delete();
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Desvinculación WordPress',
                'description' => "Se desvinculó el curso {$course->name} de WordPress",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->back()->with('success', 'Enlace con WordPress eliminado correctamente.');
        }

        // Buscar el nombre del curso en WordPress
        $wpCourseName = 'Curso WordPress #' . $wpCourseId;
        try {
            $wpCourses = $wpService->getSgaCourses();
            foreach ($wpCourses as $wpC) {
                if (($wpC['wp_course_id'] ?? null) == $wpCourseId) {
                    $wpCourseName = $wpC['wp_course_name'] ?? $wpCourseName;
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo resolver nombre de curso WP: ' . $e->getMessage());
        }

        CourseMapping::updateOrCreate(
            ['course_id' => $course->id],
            [
                'wp_course_id' => $wpCourseId,
                'wp_course_name' => $wpCourseName,
            ]
        );

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Vinculación WordPress',
            'description' => "Se vinculó el curso {$course->name} con el curso WP {$wpCourseName} (ID: {$wpCourseId})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', "Curso enlazado con éxito a WordPress ({$wpCourseName}).");
    }

    /**
     * Obtener lista de cursos del Aula Virtual Moodle.
     */
    public function getMoodleCourses(MoodleApiService $moodleService)
    {
        try {
            $courses = $moodleService->getCourses();
            return response()->json([
                'success' => true,
                'data' => $courses,
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicCourseController@getMoodleCourses Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con Moodle: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Enlazar entidad con Moodle (Cascada: Curso, Módulo o Sección).
     */
    public function linkMoodle(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:course,module,schedule',
            'id' => 'required|integer',
            'moodle_course_id' => 'nullable|integer',
        ]);

        $type = $validated['type'];
        $id = $validated['id'];
        $moodleCourseId = $validated['moodle_course_id'] ?: null;

        if ($type === 'course') {
            $entity = Course::findOrFail($id);
            $typeName = 'Curso';
        } elseif ($type === 'module') {
            $entity = Module::findOrFail($id);
            $typeName = 'Módulo';
        } elseif ($type === 'schedule') {
            $entity = CourseSchedule::findOrFail($id);
            $typeName = 'Sección';
        }

        $entity->moodle_course_id = $moodleCourseId;
        $entity->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Vinculación Moodle',
            'description' => "Se actualizó el enlace Moodle de {$typeName} {$entity->id} (Moodle ID: " . ($moodleCourseId ?: 'Desvinculado') . ")",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $statusText = $moodleCourseId ? "enlazado con Moodle ID: {$moodleCourseId}" : 'desvinculado de Moodle';
        return redirect()->back()->with('success', "{$typeName} {$statusText} correctamente.");
    }

    /**
     * Obtener horarios de WordPress para el curso mapeado de una sección.
     */
    public function getWpSchedules(CourseSchedule $schedule, WordpressApiService $wpService)
    {
        $courseMapping = $schedule->module?->course?->mapping;

        if (!$courseMapping || empty($courseMapping->wp_course_id)) {
            return response()->json([
                'success' => false,
                'message' => 'El curso principal aún no está enlazado con ningún curso de WordPress.',
                'data' => [],
            ], 422);
        }

        try {
            $schedules = $wpService->getSchedulesForWpCourse((int) $courseMapping->wp_course_id);
            return response()->json([
                'success' => true,
                'wp_course_name' => $courseMapping->wp_course_name,
                'wp_course_id' => $courseMapping->wp_course_id,
                'data' => $schedules,
            ]);
        } catch (\Exception $e) {
            Log::error('AcademicCourseController@getWpSchedules Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios de WordPress: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Vincular sección con un horario de WordPress (ScheduleMapping).
     */
    public function linkWpSchedule(Request $request, CourseSchedule $schedule)
    {
        $validated = $request->validate([
            'wp_schedule_string' => 'nullable|string|max:255',
        ]);

        $wpScheduleString = $validated['wp_schedule_string'] ?? null;

        if (empty($wpScheduleString)) {
            ScheduleMapping::where('course_schedule_id', $schedule->id)->delete();
            return redirect()->back()->with('success', 'Enlace de horario con WordPress eliminado.');
        }

        $wpCourseId = $schedule->module?->course?->mapping?->wp_course_id ?? 0;

        ScheduleMapping::updateOrCreate(
            ['course_schedule_id' => $schedule->id],
            [
                'wp_schedule_string' => $wpScheduleString,
                'wp_course_id' => $wpCourseId,
            ]
        );

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Vinculación Horario WP',
            'description' => "Se enlazó la sección {$schedule->id} con el horario WP '{$wpScheduleString}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Sección vinculada al horario de WordPress exitosamente.');
    }
}
