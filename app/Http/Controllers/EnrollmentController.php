<?php

namespace App\Http\Controllers;

// INICIO DE LA CORRECCIÓN
use Illuminate\Routing\Controller; // <-- ESTA LÍNEA FALTABA
// FIN DE LA CORRECCIÓN

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
// ... (el resto de tus 'use' statements) ...
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment; // <-- IMPORTAR
use App\Models\User; // <-- IMPORTAR
// --- INICIO DE LA MODIFICACIÓN (Laravel) ---
use App\Models\CourseMapping;
use App\Models\ScheduleMapping; // <-- Importar el nuevo modelo
// --- FIN DE LA MODIFICACIÓN (Laravel) ---
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // <-- IMPORTAR
use Illuminate\Support\Facades\Log; // <-- AÑADIDO PARA LOGGING
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon; // <-- IMPORTAR

class EnrollmentController extends Controller
{
    /**
     * Maneja la recepción de una nueva inscripción desde la API (página web).
     * Este es el núcleo de la lógica que solicitaste.
     */
    public function store(Request $request): JsonResponse
    {
        // --- LOG DE DIAGNÓSTICO ELIMINADO ---
        // Log::info('EnrollmentController@store: Datos crudos recibidos.', $request->all());
        // --- FIN DE LOG DE DIAGNÓSTICO ---


        // 1. Validar datos básicos para identificar al estudiante
        $preValidator = Validator::make($request->all(), [
            'cedula' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        if ($preValidator->fails()) {
            // LOG AÑADIDO
            Log::warning('EnrollmentController: Falló el PreValidator (cédula/email).', ['errors' => $preValidator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'La cédula y el email son requeridos.',
                'errors' => $preValidator->errors()
            ], 422);
        }

        // 2. Regla especial: Si el estudiante YA existe (por cédula o email)
        // CORRECCIÓN: Tu migración 'create_students_table' usa 'cedula', no 'document_number'.
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

            // --- INICIO DE LA MODIFICACIÓN (Laravel) ---
            'wp_course_id' => 'required|integer', // ID de WP ahora es requerido
            'course_name_from_wp' => 'nullable|string', // Nombre de WP para logs
            
            // CORRECCIÓN DE TYPO: Se alinea con el campo enviado por WP
            'wp_schedule_string' => 'required|string|max:255', // String crudo de WP
            
            // 'schedule_string_from_wp' => 'required|string|max:255', // <-- ANTERIOR (INCORRECTO)
            // 'course_name' => 'required|string|max:255', // <-- ELIMINADO
            // 'schedule_string' => 'required|string|max:255', // <-- ELIMINADO
            // --- FIN DE LA MODIFICACIÓN (Laravel) ---
            
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
            // LOG AÑADIDO
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
            $result = DB::transaction(function () use ($data, $isMinor) {
                
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
                    
                    // =================================================================
                    // CORRECCIÓN: Columna 'balance' no existe en tu migración.
                    // (Línea eliminada)
                    // =================================================================
                    
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

                // --- INICIO DE LA MODIFICACIÓN (Laravel) ---
                
                // 7. Encontrar el Curso (Course)
                $course = $this->findCourseFromWpId($data['wp_course_id'], $data['course_name_from_wp'] ?? null);

                // 8. Encontrar el Horario (CourseSchedule) usando el mapeo
                // CORRECCIÓN DE TYPO: Usar el campo correcto de $data
                $schedule = $this->findScheduleFromWpString($data['wp_schedule_string']);
                
                // 9. VERIFICACIÓN: Asegurarse de que el horario pertenece al curso
                $course_modules_ids = $course->modules()->pluck('id');
                if (!$course_modules_ids->contains($schedule->module_id)) {
                    // CORRECCIÓN DE TYPO: Mostrar el valor correcto en el error
                    throw new \Exception("Conflicto de Mapeo: El horario '{$data['wp_schedule_string']}' (ID: {$schedule->id}) no pertenece al curso '{$course->name}'.");
                }

                // 10. Validar reglas de negocio para la sección
                // (La regla de 'balance' se omitirá para estudiantes nuevos)
                $this->validateCourseRules($schedule, $student, true); // true = esNuevoEstudiante
                
                // 11. Crear la Inscripción (Enrollment)
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id, 
                    'status' => 'Pendiente', // <-- CORREGIDO: Debe estar pendiente, igual que el pago
                    'final_grade' => null,
                ]);

                // 12. Crear el registro de Pago (Payment) pendiente
                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    // Usamos el 'payment_concept_id' y 'price' del MÓDULO al que pertenece el horario
                    'payment_concept_id' => $schedule->module->payment_concept_id ?? null,
                    'amount' => $schedule->module->price ?? 0,
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // <-- PAGO PENDIENTE
                    'gateway' => 'Por Pagar',
                ]);
                
                // --- FIN DE LA MODIFICACIÓN (Laravel) ---

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
            \Log::error("Error en EnrollmentController@store (Nuevo Estudiante): " . $e->getMessage());
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
            // --- INICIO DE LA MODIFICACIÓN (Laravel) ---
            'wp_course_id' => 'required|integer', // ID de WP ahora es requerido
            'course_name_from_wp' => 'nullable|string', // Nombre de WP para logs
            
            // CORRECCIÓN DE TYPO: Se alinea con el campo enviado por WP
            'wp_schedule_string' => 'required|string|max:255', // String crudo de WP
            
            // 'schedule_string_from_wp' => 'required|string|max:255', // <-- ANTERIOR (INCORRECTO)
            // 'course_name' => 'required|string|max:255', // <-- ELIMINADO
            // 'schedule_string' => 'required|string|max:255', // <-- ELIMINADO
            // --- FIN DE LA MODIFICACIÓN (Laravel) ---
        ]);

        if ($validator->fails()) {
            // LOG AÑADIDO
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

                // --- INICIO DE LA MODIFICACIÓN (Laravel) ---
                
                // 2. Encontrar el Curso (Course)
                $course = $this->findCourseFromWpId($data['wp_course_id'], $data['course_name_from_wp'] ?? null);

                // 3. Encontrar el Horario (CourseSchedule) usando el mapeo
                // CORRECCIÓN DE TYPO: Usar el campo correcto de $data
                $schedule = $this->findScheduleFromWpString($data['wp_schedule_string']);
                
                // 4. VERIFICACIÓN: Asegurarse de que el horario pertenece al curso
                $course_modules_ids = $course->modules()->pluck('id');
                if (!$course_modules_ids->contains($schedule->module_id)) {
                    // CORRECCIÓN DE TYPO: Mostrar el valor correcto en el error
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
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente', // <-- CORREGIDO: Debe estar pendiente, igual que el pago
                    'final_grade' => null,
                ]);

                // 8. Crear el registro de Pago (Payment) pendiente
                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $schedule->module->payment_concept_id ?? null,
                    'amount' => $schedule->module->price ?? 0,
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // <-- PAGO PENDIENTE
                    'gateway' => 'Por Pagar',
                ]);
                
                // --- FIN DE LA MODIFICACIÓN (Laravel) ---

                return [
                    'status' => 'success',
                    'message' => 'Inscripción pendiente de pago registrada para estudiante existente.',
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                ];
            });

            return response()->json($result, 201); // 201 Created

        } catch (\Exception $e) {
            \Log::error("Error en handleExistingStudentEnrollment: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() // Devolver el mensaje de error
            ], 400); // 400 Bad Request
        }
    }

    // --- INICIO DE NUEVA FUNCIÓN HELPER ---
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
        
        // --- INICIO DE LA MODIFICACIÓN (Laravel) ---
        // Cargar la relación con los módulos, ya que la necesitaremos
        $course->load('modules');
        // --- FIN DE LA MODIFICACIÓN (Laravel) ---
        
        return $course;
    }
    // --- FIN DE NUEVA FUNCIÓN HELPER ---

    // --- INICIO DE NUEVA FUNCIÓN HELPER (MAPEADO DE HORARIO) ---
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
    // --- FIN DE NUEVA FUNCIÓN HELPER ---


    /**
     * Helper para buscar la sección (CourseSchedule)
     * (No crea, solo busca)
     *
     * --- ESTA FUNCIÓN YA NO SE USA DIRECTAMENTE POR store(), PERO SE MANTIENE POR SI OTROS MÉTODOS LA USAN ---
     */
    private function findCourseSchedule(string $moduleName, string $sectionName): CourseSchedule
    {
        $module = Module::where('name', $moduleName)->first();
        if (!$module) {
            throw new \Exception("El módulo '{$moduleName}' no fue encontrado. Verifique el nombre.");
        }

        // --- INICIO DE LA MODIFICACIÓN (Búsqueda por Horario) ---
        // El $sectionName que llega de WP ahora es "Sábado | 09:00 AM - 12:00 PM"
        
        // 1. Buscamos por 'section_name' (de la migración 2025_11_05_000018)
        $schedule = CourseSchedule::where('module_id', $module->id)
                                     ->where('section_name', $sectionName)
                                     ->first();
        
        // 2. Fallback por si se guardó en 'days_of_week' (de la migración 2025_11_05_000015)
        if (!$schedule) {
             $schedule = CourseSchedule::where('module_id', $module->id)
                                       ->where('days_of_week', $sectionName) // Asumiendo que 'days_of_week' es un string
                                       ->first();
        }
        
        // 3. Fallback por si 'days_of_week' es un JSON
        if (!$schedule) {
             $schedule = CourseSchedule::where('module_id', $module->id)
                                       ->whereJsonContains('days_of_week', $sectionName) // Asumiendo JSON
                                       ->first();
        }

        // 4. Fallback: Búsqueda con el string de horario (schedule_string)
        // Esto es por si el campo 'schedule_string' de la tabla 'course_schedules'
        // contiene el valor "Sábado | 09:00 AM - 12:00 PM".
        if (!$schedule) {
            $schedule = CourseSchedule::where('module_id', $module->id)
                                      ->where('schedule_string', $sectionName)
                                      ->first();
        }
        // --- FIN DE LA MODIFICACIÓN (Búsqueda por Horario) ---


        if (!$schedule) {
            throw new \Exception("La sección '{$sectionName}' para '{$moduleName}' no fue encontrada.");
        }
        
        // Cargar las relaciones necesarias para las reglas
        $schedule->load('module');

        return $schedule;
    }

    /**
     * Helper para validar las 3 reglas de negocio.
     * @param bool $isNewStudent Si es true, omite la validación de balance.
     */
    private function validateCourseRules(CourseSchedule $schedule, Student $student, bool $isNewStudent = false): void
    {
        // 1. "En caso de ser estudiante existente, no debe tener balances pendientes."
        // (Asumimos que balance > 0 es tener deuda)
        // =================================================================
        // CORRECCIÓN: Tu tabla 'students' no tiene 'balance'.
        // Comentamos esta regla temporalmente.
        // =================================================================
        // if (!$isNewStudent && $student->balance > 0) {
        //     throw new \Exception('El estudiante tiene un balance pendiente y no puede inscribirse.');
        // }
        // =================================================================

        // 2. "Deben haber cupos disponibles en el curso que se solicita."
        $enrolledCount = Enrollment::where('course_schedule_id', $schedule->id)
                                     ->whereIn('status', ['Activo', 'Cursando', 'Pendiente', 'Inscrito']) // Contar pendientes e inscritos también
                                     ->count();
        if ($schedule->capacity > 0 && $enrolledCount >= $schedule->capacity) { // capacity > 0 para evitar bloqueo si es 0
            throw new \Exception('La sección está llena. No hay cupos disponibles.');
        }

        // 3. "Una vez inicia el curso, no se puede inscribir"
        if ($schedule->start_date && Carbon::now()->gt($schedule->start_date)) {
            throw new \Exception('Este curso ya ha comenzado. No se permiten nuevas inscripciones.');
        }
    }
}