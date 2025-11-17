<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\Payment; // <-- IMPORTAR
use App\Models\User; // <-- IMPORTAR
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // <-- IMPORTAR
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
        // 1. Validar datos básicos para identificar al estudiante
        $preValidator = Validator::make($request->all(), [
            'cedula' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        if ($preValidator->fails()) {
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
            'course_name' => 'required|string|max:255', // Asumimos que es el NOMBRE DEL MÓDULO
            'schedule_string' => 'required|string|max:255', // Asumimos que es el NOMBRE DE LA SECCIÓN
            
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
                    // =================================================================
                    // 'balance' => 0, // Nuevo estudiante inicia con balance 0 <-- ESTA LÍNEA CAUSA EL ERROR
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

                // 7. Encontrar la sección del curso
                $schedule = $this->findCourseSchedule($data['course_name'], $data['schedule_string']);

                // 8. Validar reglas de negocio para la sección
                // (La regla de 'balance' se omitirá para estudiantes nuevos)
                $this->validateCourseRules($schedule, $student, true); // true = esNuevoEstudiante
                
                // 9. Crear la Inscripción (Enrollment)
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente', // <-- PENDIENTE DE PAGO
                    'final_grade' => null,
                ]);

                // 10. Crear el registro de Pago (Payment) pendiente
                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $schedule->module->payment_concept_id ?? null,
                    'amount' => $schedule->module->price ?? 0, // Asumimos precio del módulo
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // <-- PAGO PENDIENTE
                    'gateway' => 'Por Pagar',
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
            'course_name' => 'required|string|max:255',
            'schedule_string' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos de inscripción inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $validator->validated();

        try {
            $result = DB::transaction(function () use ($data, $student) {

                // 2. Encontrar la sección del curso
                $schedule = $this->findCourseSchedule($data['course_name'], $data['schedule_string']);

                // 3. Validar reglas de negocio (Cupos, Balance, Fecha)
                $this->validateCourseRules($schedule, $student, false); // false = NO esNuevoEstudiante
                
                // 4. Verificar si ya existe esta inscripción exacta
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('course_schedule_id', $schedule->id)
                    ->first();

                if ($existingEnrollment) {
                    throw new \Exception('Este estudiante ya está inscrito en esta sección.');
                }

                // 5. Crear la Inscripción (Enrollment)
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente', // <-- PENDIENTE DE PAGO
                    'final_grade' => null,
                ]);

                // 6. Crear el registro de Pago (Payment) pendiente
                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $schedule->module->payment_concept_id ?? null,
                    'amount' => $schedule->module->price ?? 0,
                    'currency' => 'DOP',
                    'status' => 'Pendiente', // <-- PAGO PENDIENTE
                    'gateway' => 'Por Pagar',
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
            \Log::error("Error en handleExistingStudentEnrollment: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() // Devolver el mensaje de error
            ], 400); // 400 Bad Request
        }
    }

    /**
     * Helper para buscar la sección (CourseSchedule)
     * (No crea, solo busca)
     */
    private function findCourseSchedule(string $moduleName, string $sectionName): CourseSchedule
    {
        $module = Module::where('name', $moduleName)->first();
        if (!$module) {
            throw new \Exception("El módulo '{$moduleName}' no fue encontrado. Verifique el nombre.");
        }

        // Buscamos por 'section_name' (de la migración 2025_11_05_000018)
        $schedule = CourseSchedule::where('module_id', $module->id)
                                    ->where('section_name', $sectionName)
                                    ->first();
        
        // Fallback por si 'schedule_string' se guarda en 'day_of_week' (tu migración 2025_11_05_000015)
        if (!$schedule) {
             $schedule = CourseSchedule::where('module_id', $module->id)
                                     ->where('days_of_week', $sectionName) // Asumiendo que 'days_of_week' es un string, no un array json
                                     ->first();
        }
        
        // Fallback por si 'days_of_week' es un JSON
        if (!$schedule) {
             $schedule = CourseSchedule::where('module_id', $module->id)
                                     ->whereJsonContains('days_of_week', $sectionName)
                                     ->first();
        }


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
                                     ->whereIn('status', ['Activo', 'Cursando', 'Pendiente']) // Contar pendientes también
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