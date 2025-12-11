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
use App\Models\PaymentConcept; 
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
        // 1. Validar datos básicos
        $preValidator = Validator::make($request->all(), [
            'cedula' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        if ($preValidator->fails()) {
            Log::warning('EnrollmentController: Falló el PreValidator.', ['errors' => $preValidator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'La cédula y el email son requeridos.',
                'errors' => $preValidator->errors()
            ], 422);
        }

        $existingStudent = Student::where('cedula', $request->cedula)->first();
        $existingUser = User::where('email', $request->email)->first();

        if ($existingStudent || $existingUser) {
            return $this->handleExistingStudentEnrollment($request, $existingStudent, $existingUser);
        }

        // 3. Validar datos completos para nuevo estudiante
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cedula' => 'required|string|max:20|unique:students,cedula',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'wp_course_id' => 'required|integer',
            'course_name_from_wp' => 'nullable|string',
            'wp_schedule_string' => 'required|string|max:255',
            'is_minor_flag' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'how_found' => 'nullable|string|max:100',
            'tutor_name' => 'nullable|string',
            'tutor_cedula' => 'nullable|string',
            'tutor_phone' => 'nullable|string',
            'tutor_relationship' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $isMinor = !empty($request->is_minor_flag) && $request->is_minor_flag !== 'No soy menor';

        try {
            $result = DB::transaction(function () use ($data, $request, $isMinor) {
                
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['cedula']),
                    'access_expires_at' => Carbon::now()->addMonths(3),
                ]);
                $user->assignRole('Estudiante');

                $student = Student::create([
                    'user_id' => $user->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'cedula' => $data['cedula'],
                    'email' => $data['email'],
                    'home_phone' => $data['phone'],
                    'mobile_phone' => $request->mobile_phone ?? $data['phone'],
                    'status' => 'Activo',
                    'city' => $data['city'] ?? null,
                    'sector' => $data['sector'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'how_found' => $data['how_found'] ?? null,
                    'is_minor' => $isMinor,
                    'tutor_name' => $data['tutor_name'] ?? null,
                    'tutor_cedula' => $data['tutor_cedula'] ?? null,
                    'tutor_phone' => $data['tutor_phone'] ?? null,
                    'tutor_relationship' => $data['tutor_relationship'] ?? null,
                ]);

                $course = $this->findCourseFromWpId($data['wp_course_id']);
                $schedule = $this->findScheduleFromWpString($data['wp_schedule_string']);
                
                $this->validateCourseRules($schedule, $student, true);
                
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'course_schedule_id' => $schedule->id, 
                    'status' => 'Pendiente',
                    'enrollment_date' => now(),
                ]);

                // --- GENERAR PAGO DE INSCRIPCIÓN (LÓGICA CORREGIDA) ---
                
                // 1. Obtener concepto de Inscripción (Sin amount)
                $concept = PaymentConcept::firstOrCreate(['name' => 'Inscripción']); 

                // 2. Usar el precio del CURSO
                $amount = $course->registration_fee ?? 0;

                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $concept->id,
                    'amount' => $amount, 
                    'currency' => 'DOP',
                    'status' => 'Pendiente',
                    'gateway' => 'Por Pagar',
                    'due_date' => now()->addDays(3),
                ]);
                
                return [
                    'status' => 'success',
                    'message' => 'Pre-inscripción realizada exitosamente.',
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                ];
            });

            return response()->json($result, 201);

        } catch (\Exception $e) {
            Log::error("Error en EnrollmentController: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    private function handleExistingStudentEnrollment(Request $request, $existingStudent, $existingUser)
    {
        if ($existingStudent && $existingUser && $existingStudent->user_id != $existingUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conflicto de datos. La cédula y el email pertenecen a cuentas diferentes.'
            ], 409); 
        }

        $student = $existingStudent ?? $existingUser->student;

        if (!$student) {
             return response()->json([
                'status' => 'error',
                'message' => 'No se pudo encontrar el perfil de estudiante asociado.'
            ], 404);
        }

         $validator = Validator::make($request->all(), [
            'wp_course_id' => 'required|integer', 
            'course_name_from_wp' => 'nullable|string', 
            'wp_schedule_string' => 'required|string|max:255', 
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

                $course = $this->findCourseFromWpId($data['wp_course_id'], $data['course_name_from_wp'] ?? null);
                $schedule = $this->findScheduleFromWpString($data['wp_schedule_string']);
                
                $course_modules_ids = $course->modules()->pluck('id');
                if (!$course_modules_ids->contains($schedule->module_id)) {
                    throw new \Exception("Conflicto de Mapeo: El horario '{$data['wp_schedule_string']}' (ID: {$schedule->id}) no pertenece al curso '{$course->name}'.");
                }

                $this->validateCourseRules($schedule, $student, false);
                
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('course_schedule_id', $schedule->id) 
                    ->first();

                if ($existingEnrollment) {
                    throw new \Exception('Este estudiante ya está inscrito en esta sección.');
                }

                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Pendiente', 
                    'final_grade' => null,
                    'enrollment_date' => now(),
                ]);

                // 8. Crear el registro de Pago (Payment) - CORREGIDO
                
                $inscriptionConcept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción'],
                    ['description' => 'Pago único de inscripción al curso'] // Sin amount
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

            return response()->json($result, 201);

        } catch (\Exception $e) {
            Log::error("Error en handleExistingStudentEnrollment: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() 
            ], 400); 
        }
    }

    private function findCourseFromWpId(int $wp_course_id, ?string $wp_course_name = ''): Course
    {
        $mapping = CourseMapping::where('wp_course_id', $wp_course_id)->first();
        if (!$mapping) {
            throw new \Exception("Error de Mapeo: El ID de curso de WordPress '{$wp_course_id}' no está enlazado.");
        }

        $course = Course::find($mapping->course_id);
        if (!$course) {
            throw new \Exception("Error de Base de Datos: Curso interno no encontrado.");
        }
        
        $course->load('modules');
        return $course;
    }

    private function findScheduleFromWpString(string $wp_schedule_string): CourseSchedule
    {
        $mapping = ScheduleMapping::where('wp_schedule_string', $wp_schedule_string)->first();
        
        if (!$mapping) {
            throw new \Exception("Error de Mapeo: El horario '{$wp_schedule_string}' no está enlazado.");
        }

        $schedule = CourseSchedule::with('module')->find($mapping->course_schedule_id);

        if (!$schedule) {
            throw new \Exception("Error de Base de Datos: Horario interno no encontrado.");
        }
        
        return $schedule;
    }

    private function validateCourseRules(CourseSchedule $schedule, Student $student, bool $isNewStudent = false): void
    {
        $enrolledCount = Enrollment::where('course_schedule_id', $schedule->id)
                                     ->whereIn('status', ['Activo', 'Cursando', 'Pendiente', 'Inscrito']) 
                                     ->count();
        if ($schedule->capacity > 0 && $enrolledCount >= $schedule->capacity) { 
            throw new \Exception('La sección está llena. No hay cupos disponibles.');
        }

        if ($schedule->start_date && Carbon::now()->gt($schedule->start_date)) {
            throw new \Exception('Este curso ya ha comenzado. No se permiten nuevas inscripciones.');
        }
    }
}