<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\CourseMapping;
use App\Models\ScheduleMapping;
use App\Models\PaymentConcept; // <-- IMPORTAR
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * Maneja la recepción de una nueva inscripción desde la API (página web).
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validar datos básicos para identificar al estudiante
        $preValidator = Validator::make($request->all(), [
            'cedula' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        if ($preValidator->fails()) {
            Log::warning('EnrollmentController: Falló el PreValidator (cédula/email).', ['errors' => $preValidator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'La cédula y el email son requeridos.',
                'errors' => $preValidator->errors()
            ], 422);
        }

        // 2. Regla especial: Si el estudiante YA existe (por cédula o email)
        $existingStudent = Student::where('cedula', $request->cedula)->first();
        $existingUser = User::where('email', $request->email)->first();

        if ($existingStudent || $existingUser) {
            // Si el estudiante ya existe, llamamos a la función separada
            return $this->handleExistingStudentEnrollment($request, $existingStudent, $existingUser);
        }

        // 3. Validar datos completos para un estudiante NUEVO
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cedula' => 'required|string|max:20|unique:students,cedula', // Cédula única
            'email' => 'required|email|max:255|unique:users,email', // Email único
            'phone' => 'required|string|max:20', // 'phone' se usará para 'home_phone'
            'mobile_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',

            'wp_course_id' => 'required|integer', // ID de WP ahora es requerido
            'course_name_from_wp' => 'nullable|string', // Nombre de WP para logs
            'wp_schedule_string' => 'required|string|max:255', // String crudo de WP
            
            // Campos adicionales de tu validador
            'is_minor_flag' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'how_found' => 'nullable|string|max:100',

            // Campos de tutor (si 'is_minor_flag' está presente)
            'tutor_name' => 'nullable|string',
            'tutor_cedula' => 'nullable|string',
            'tutor_phone' => 'nullable|string',
            'tutor_relationship' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('EnrollmentController: Falló el validador de nuevo estudiante.', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Datos de entrada inválidos para nuevo estudiante.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $isMinor = !empty($data['is_minor_flag']) && $data['is_minor_flag'] !== 'No soy menor';

        // 4. Lógica para Estudiante NUEVO
        try {
            $result = DB::transaction(function () use ($data, $request, $isMinor) {
                
                // 5. Crear el Usuario (User) con acceso temporal
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'], // Email personal como login
                    'password' => Hash::make($data['cedula']), // Usar la cédula como contraseña inicial
                    'access_expires_at' => Carbon::now()->addMonths(3), // <-- ACCESO TEMPORAL
                ]);
                $user->assignRole('Estudiante');

                // 6. Crear el Estudiante (Student)
                $student = Student::create([
                    'user_id' => $user->id, // <-- VINCULAR AL USUARIO
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'cedula' => $data['cedula'],
                    'email' => $data['email'],
                    'home_phone' => $data['phone'], // Campo 'phone' del form
                    'mobile_phone' => $data['mobile_phone'] ?? $data['phone'], // Celular o el 'phone'
                    'address' => $data['address'] ?? null,
                    'status' => 'Activo', // Status del estudiante
                    
                    // Campos adicionales
                    'city' => $data['city'] ?? null,
                    'sector' => $data['sector'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'how_found' => $data['how_found'] ?? null,
                    'is_minor' => $isMinor,

                    // Campos de tutor
                    'tutor_name' => $data['tutor_name'] ?? null,
                    'tutor_cedula' => $data['tutor_cedula'] ?? null,
                    'tutor_phone' => $data['tutor_phone'] ?? null,
                    'tutor_relationship' => $data['tutor_relationship'] ?? null,
                ]);

                // 7. Encontrar el Curso (Course)
                $course = $this->findCourseFromWpId($data['wp_course_id'], $data['course_name_from_wp'] ?? null);

                // 8. Encontrar el Horario (CourseSchedule) usando el mapeo
                $schedule = $this->findScheduleFromWpString($data['wp_schedule_string']);
                
                // 9. VERIFICACIÓN: Asegurarse de que el horario pertenece al curso
                $course_modules_ids = $course->modules()->pluck('id');
                if (!$course_modules_ids->contains($schedule->module_id)) {
                    throw new \Exception("Conflicto de Mapeo: El horario '{$data['wp_schedule_string']}' (ID: {$schedule->id}) no pertenece al curso '{$course->name}'.");
                }

                // 10. Validar reglas de negocio para la sección
                $this->validateCourseRules($schedule, $student, true); // true = esNuevoEstudiante
                
                // 11. Crear la Inscripción (Enrollment)
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id, // <-- Guardamos el curso padre
                    'course_schedule_id' => $schedule->id, 
                    'status' => 'Pendiente', // <-- CORREGIDO: Debe estar pendiente, igual que el pago
                    'final_grade' => null,
                    'enrollment_date' => now(),
                ]);

                // 12. Crear el registro de Pago (Payment) - MODIFICADO PRECIO INSCRIPCIÓN
                
                // Buscar o crear el concepto de Inscripción
                $inscriptionConcept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción'],
                    ['description' => 'Pago único de inscripción al curso', 'amount' => 0]
                );

                // Usar el registration_fee del Curso
                $amount = $course->registration_fee ?? 0;

                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $inscriptionConcept->id,
                    'amount' => $amount, 
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // <-- PAGO PENDIENTE
                    'gateway' => 'Por Pagar',
                    'due_date' => now()->addDays(3),
                ]);
                
                return [
                    'status' => 'success',
                    'message' => 'Pre-inscripción realizada. Usuario temporal creado por 3 meses.',
                    'student_id' => $student->id,
                    'user_id' => $user->id,
                    'enrollment_id' => $enrollment->id,
                ];
            });

            return response()->json($result, 201); // 201 Created

        } catch (\Exception $e) {
            Log::error("Error en EnrollmentController@store (Nuevo Estudiante): " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() // Devolver el mensaje de error de la regla de negocio
            ], 400); // 400 Bad Request (falló la regla de negocio)
        }
    }

    /**
     * Maneja la lógica de inscripción para un estudiante que ya existe en el sistema.
     */
    private function handleExistingStudentEnrollment(Request $request, $existingStudent, $existingUser)
    {
        // Validar que el estudiante y el usuario coincidan si ambos existen
        if ($existingStudent && $existingUser && $existingStudent->user_id != $existingUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conflicto de datos. La cédula y el email pertenecen a cuentas diferentes.'
            ], 409); // 409 Conflict
        }

        // El estudiante es la fuente de verdad.
        $student = $existingStudent ?? $existingUser->student;

        if (!$student) {
             return response()->json([
                'status' => 'error',
                'message' => 'No se pudo encontrar el perfil de estudiante asociado.'
            ], 404); // 404 Not Found
        }

        // 1. Validar datos de la solicitud de inscripción
         $validator = Validator::make($request->all(), [
            'wp_course_id' => 'required|integer', 
            'course_name_from_wp' => 'nullable|string', 
            'wp_schedule_string' => 'required|string|max:255', 
        ]);

        if ($validator->fails()) {
            Log::warning('EnrollmentController: Falló el validador de estudiante existente.', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Datos de inscripción inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $validator->validated();

        try {
            $result = DB::transaction(function () use ($data, $student) {

                // 2. Encontrar el Curso (Course)
                $course = $this->findCourseFromWpId($data['wp_course_id'], $data['course_name_from_wp'] ?? null);

                // 3. Encontrar el Horario (CourseSchedule) usando el mapeo
                $schedule = $this->findScheduleFromWpString($data['wp_schedule_string']);
                
                // 4. VERIFICACIÓN: Asegurarse de que el horario pertenece al curso
                $course_modules_ids = $course->modules()->pluck('id');
                if (!$course_modules_ids->contains($schedule->module_id)) {
                    throw new \Exception("Conflicto de Mapeo: El horario '{$data['wp_schedule_string']}' (ID: {$schedule->id}) no pertenece al curso '{$course->name}'.");
                }

                // 5. Validar reglas de negocio (Cupos, Balance, Fecha)
                $this->validateCourseRules($schedule, $student, false); // false = NO esNuevoEstudiante
                
                // 6. Verificar si ya existe esta inscripción exacta
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('course_schedule_id', $schedule->id) 
                    ->first();

                if ($existingEnrollment) {
                    throw new \Exception('Este estudiante ya está inscrito en esta sección.');
                }

                // 7. Crear la Inscripción (Enrollment)
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente', 
                    'final_grade' => null,
                    'enrollment_date' => now(),
                ]);

                // 8. Crear el registro de Pago (Payment) - MODIFICADO PRECIO INSCRIPCIÓN
                
                $inscriptionConcept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción'],
                    ['description' => 'Pago único de inscripción al curso', 'amount' => 0]
                );

                $amount = $course->registration_fee ?? 0;

                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $inscriptionConcept->id,
                    'amount' => $amount,
                    'currency' => 'DOP',
                    'status' => 'Pendiente', 
                    'gateway' => 'Por Pagar',
                    'due_date' => now()->addDays(3),
                ]);
                
                return [
                    'status' => 'success',
                    'message' => 'Inscripción pendiente de pago registrada para estudiante existente.',
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                ];
            });

            return response()->json($result, 201); // 201 Created

        } catch (\Exception $e) {
            Log::error("Error en handleExistingStudentEnrollment: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() 
            ], 400); // 400 Bad Request
        }
    }

    /**
     * Helper para buscar el Curso (Course) interno
     * usando el ID de WordPress y la tabla de mapeo.
     */
    private function findCourseFromWpId(int $wp_course_id, ?string $wp_course_name = ''): Course
    {
        $mapping = CourseMapping::where('wp_course_id', $wp_course_id)->first();
        if (!$mapping) {
            throw new \Exception("Error de Mapeo: El ID de curso de WordPress '{$wp_course_id}' (Nombre: '{$wp_course_name}') no está enlazado en la tabla 'course_mappings'.");
        }

        $course = Course::find($mapping->course_id);
        if (!$course) {
            throw new \Exception("Error de Base de Datos: El curso interno (ID: {$mapping->course_id}) enlazado al ID de WP '{$wp_course_id}' no fue encontrado.");
        }
        
        // Cargar la relación con los módulos
        $course->load('modules');
        
        return $course;
    }

    /**
     * Helper para buscar la sección (CourseSchedule)
     * usando el string de WP y la tabla 'schedule_mappings'.
     */
    private function findScheduleFromWpString(string $wp_schedule_string): CourseSchedule
    {
        $mapping = ScheduleMapping::where('wp_schedule_string', $wp_schedule_string)->first();
        
        if (!$mapping) {
            throw new \Exception("Error de Mapeo: El horario '{$wp_schedule_string}' no está enlazado en la tabla 'schedule_mappings'.");
        }

        // Cargamos la relación con el módulo para usarla después
        $schedule = CourseSchedule::with('module')->find($mapping->course_schedule_id);

        if (!$schedule) {
            throw new \Exception("Error de Base de Datos: El horario interno (ID: {$mapping->course_schedule_id}) enlazado a '{$wp_schedule_string}' no fue encontrado.");
        }
        
        return $schedule;
    }

    /**
     * Helper para buscar la sección (CourseSchedule)
     * (No crea, solo busca) - Mantenido por compatibilidad
     */
    private function findCourseSchedule(string $moduleName, string $sectionName): CourseSchedule
    {
        $module = Module::where('name', $moduleName)->first();
        if (!$module) {
            throw new \Exception("El módulo '{$moduleName}' no fue encontrado. Verifique el nombre.");
        }

        // 1. Buscamos por 'section_name'
        $schedule = CourseSchedule::where('module_id', $module->id)
                                     ->where('section_name', $sectionName)
                                     ->first();
        
        // 2. Fallback 'days_of_week' string
        if (!$schedule) {
             $schedule = CourseSchedule::where('module_id', $module->id)
                                     ->where('days_of_week', $sectionName) 
                                     ->first();
        }
        
        // 3. Fallback 'days_of_week' json
        if (!$schedule) {
             $schedule = CourseSchedule::where('module_id', $module->id)
                                     ->whereJsonContains('days_of_week', $sectionName)
                                     ->first();
        }

        if (!$schedule) {
            throw new \Exception("La sección '{$sectionName}' para '{$moduleName}' no fue encontrada.");
        }
        
        $schedule->load('module');

        return $schedule;
    }

    /**
     * Helper para validar las reglas de negocio.
     * @param bool $isNewStudent Si es true, omite la validación de balance.
     */
    private function validateCourseRules(CourseSchedule $schedule, Student $student, bool $isNewStudent = false): void
    {
        // 1. Balance: Comentado temporalmente por falta de columna
        // if (!$isNewStudent && $student->balance > 0) {
        //    throw new \Exception('El estudiante tiene un balance pendiente y no puede inscribirse.');
        // }

        // 2. Cupos
        $enrolledCount = Enrollment::where('course_schedule_id', $schedule->id)
                                     ->whereIn('status', ['Activo', 'Cursando', 'Pendiente', 'Inscrito']) 
                                     ->count();
        if ($schedule->capacity > 0 && $enrolledCount >= $schedule->capacity) { 
            throw new \Exception('La sección está llena. No hay cupos disponibles.');
        }

        // 3. Fecha
        if ($schedule->start_date && Carbon::now()->gt($schedule->start_date)) {
            throw new \Exception('Este curso ya ha comenzado. No se permiten nuevas inscripciones.');
        }
    }
}