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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TeacherController extends Controller
{
    /**
     * Directorio y listado de docentes institucionales.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = User::role('Profesor')
            ->withCount('schedules');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate(15)->withQueryString();

        // Métricas de profesores
        $totalTeachers = User::role('Profesor')->count();
        $teachersWithLoad = User::role('Profesor')->has('schedules')->count();
        $totalSchedules = CourseSchedule::whereNotNull('teacher_id')->count();

        return Inertia::render('Teachers/Index', [
            'teachers' => $teachers,
            'filters' => [
                'search' => $search,
            ],
            'metrics' => [
                'total' => $totalTeachers,
                'with_load' => $teachersWithLoad,
                'total_schedules' => $totalSchedules,
            ],
        ]);
    }

    /**
     * Registro de nuevo docente con cuenta y rol de Profesor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:150',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre completo del profesor es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya pertenece a otro usuario en el sistema.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener un mínimo de 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('Profesor');
                }

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Creación de Docente',
                    'description' => "Se registró nuevo profesor {$user->name} ({$user->email})",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Profesor registrado exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error registrando docente: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al registrar docente: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualización de datos del docente.
     */
    public function update(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:150',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($teacher->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está en uso por otro usuario.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        try {
            DB::transaction(function () use ($teacher, $validated, $request) {
                $updateData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ];

                if (!empty($validated['password'])) {
                    $updateData['password'] = Hash::make($validated['password']);
                }

                $teacher->update($updateData);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Actualización de Docente',
                    'description' => "Se actualizaron los datos del profesor {$teacher->name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Datos del profesor actualizados exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error actualizando docente: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al actualizar docente: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminación de profesor o revocación de rol.
     */
    public function destroy(Request $request, User $teacher)
    {
        try {
            $assignedSchedulesCount = $teacher->schedules()->count();
            if ($assignedSchedulesCount > 0) {
                return redirect()->back()->withErrors([
                    'error' => "No se puede eliminar al docente porque tiene {$assignedSchedulesCount} sección(es) asignada(s). Desasigne sus horarios primero."
                ]);
            }

            DB::transaction(function () use ($teacher, $request) {
                $name = $teacher->name;
                $teacher->delete();

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Eliminación de Docente',
                    'description' => "Se eliminó al docente {$name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->route('admin.teachers.index')->with('success', 'Docente eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al eliminar docente: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al eliminar docente: ' . $e->getMessage()]);
        }
    }

    /**
     * Perfil 360° y carga académica del profesor.
     */
    public function profile(User $teacher)
    {
        if (!$teacher->hasRole('Profesor')) {
            abort(404, 'Usuario no encontrado o no posee el rol de Profesor.');
        }

        // Carga académica: secciones asignadas con módulos, cursos y conteo de alumnos
        $schedules = $teacher->schedules()
            ->with([
                'module.course',
                'classroom:id,name',
                'enrollments' => function ($q) {
                    $q->whereIn('status', ['Activo', 'Cursando', 'Completado'])
                      ->with('student:id,first_name,last_name,email,mobile_phone,student_code');
                }
            ])
            ->orderBy('id', 'desc')
            ->get();

        // Alumnos únicos atendidos
        $studentIds = collect();
        foreach ($schedules as $sch) {
            foreach ($sch->enrollments as $enr) {
                if ($enr->student_id) {
                    $studentIds->push($enr->student_id);
                }
            }
        }
        $uniqueStudentsCount = $studentIds->unique()->count();

        // Cursos activos para crear nuevas secciones
        $courses = Course::select('id', 'name', 'code')->orderBy('name')->get();

        // Horarios huérfanos sin profesor asignado
        $unassignedSchedules = CourseSchedule::whereNull('teacher_id')
            ->with('module.course', 'classroom')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($sch) {
                return [
                    'id' => $sch->id,
                    'section_name' => $sch->section_name ?: "Sección #{$sch->id}",
                    'course_name' => $sch->module?->course?->name ?? 'General',
                    'module_name' => $sch->module?->name ?? 'Módulo General',
                    'classroom_name' => $sch->classroom?->name ?? 'Virtual / Por asignar',
                    'days' => $sch->days_of_week ?? [],
                    'start_time' => $sch->start_time ? Carbon::parse($sch->start_time)->format('h:i A') : '',
                    'end_time' => $sch->end_time ? Carbon::parse($sch->end_time)->format('h:i A') : '',
                ];
            });

        $classrooms = Classroom::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Teachers/Profile', [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'metrics' => [
                'total_sections' => $schedules->count(),
                'total_students' => $uniqueStudentsCount,
            ],
            'courses' => $courses,
            'unassignedSchedules' => $unassignedSchedules,
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Asignar sección existente huérfana al profesor.
     */
    public function assignSchedule(Request $request, User $teacher)
    {
        $request->validate([
            'course_schedule_id' => 'required|exists:course_schedules,id',
        ]);

        $schedule = CourseSchedule::findOrFail($request->course_schedule_id);

        if ($schedule->teacher_id && $schedule->teacher_id !== $teacher->id) {
            return redirect()->back()->withErrors(['error' => 'Esta sección ya está asignada a otro docente.']);
        }

        $schedule->teacher_id = $teacher->id;
        $schedule->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Asignación de Horario',
            'description' => "Se asignó la sección #{$schedule->id} al profesor {$teacher->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Sección asignada al docente exitosamente.');
    }

    /**
     * Crear una nueva sección y asignarla inmediatamente al profesor.
     */
    public function createSchedule(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'section_name' => 'required|string|max:100',
            'days_of_week' => 'required|array|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'nullable|integer|min:1',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'modality' => 'nullable|string|max:50',
        ]);

        try {
            DB::transaction(function () use ($validated, $teacher, $request) {
                $schedule = CourseSchedule::create([
                    'module_id' => $validated['module_id'],
                    'teacher_id' => $teacher->id,
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

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Creación de Sección Docente',
                    'description' => "Se creó la sección {$schedule->section_name} asignada a {$teacher->name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            });

            return redirect()->back()->with('success', 'Nueva sección académica creada y asignada con éxito.');
        } catch (\Exception $e) {
            Log::error("Error creando sección: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al crear la sección: ' . $e->getMessage()]);
        }
    }

    /**
     * Desasignar profesor de una sección académica.
     */
    public function unassignSchedule(Request $request, User $teacher, CourseSchedule $schedule)
    {
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'La sección no pertenece a este docente.');
        }

        $schedule->teacher_id = null;
        $schedule->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Desasignación de Horario',
            'description' => "Se desvinculó al profesor {$teacher->name} de la sección #{$schedule->id}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Sección desvinculada del docente exitosamente.');
    }

    /**
     * Módulos de un curso específico para select dependiente en modales.
     */
    public function courseModules(Course $course)
    {
        $modules = Module::where('course_id', $course->id)
            ->select('id', 'name', 'code', 'price')
            ->orderBy('name')
            ->get();

        return response()->json($modules);
    }
}
