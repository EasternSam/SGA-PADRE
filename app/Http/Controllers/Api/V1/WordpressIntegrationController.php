<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\User;
use App\Models\CourseMapping;
use App\Models\Enrollment;
use App\Models\Payment; // Importar Payment

class WordpressIntegrationController extends Controller
{
    /**
     * Maneja la solicitud de una nueva inscripción desde WordPress (Fluent Forms).
     * Este controlador SÍ usa la lógica de CourseMapping (Punto 3).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleNewInscription(Request $request)
    {
        // 1. Validar los datos de entrada que vienen de WordPress
        $validator = Validator::make($request->all(), [
            'cedula'       => 'required|string|max:20',
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'wp_course_id' => 'required|integer', // <-- El ID del CPT 'curso' de WP
            
            // Campos opcionales que enviaremos desde WP
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:255',
            'sector'       => 'nullable|string|max:255',
            'birth_date'   => 'nullable|date',
            'gender'       => 'nullable|string|max:50',
            'nationality'  => 'nullable|string|max:100',
            'how_found'    => 'nullable|string|max:100',
            'is_minor_flag'=> 'nullable|string',
            'tutor_cedula' => 'nullable|string',
            'tutor_name'   => 'nullable|string',
            'tutor_phone'  => 'nullable|string',
            'tutor_relationship' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('API WP->Laravel (V1): Validación fallida', $request->all());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        Log::info('API WP->Laravel (V1): Petición de inscripción recibida', $data);
        
        $isMinor = !empty($data['is_minor_flag']) && $data['is_minor_flag'] !== 'No soy menor';

        try {
            // 2. Encontrar el curso de Laravel usando el Mapeo (Punto 3)
            $mapping = CourseMapping::with('course.modules')->where('wp_course_id', $data['wp_course_id'])->first();

            if (!$mapping) {
                Log::error("API WP->Laravel (V1): No se encontró mapeo para el wp_course_id: {$data['wp_course_id']}");
                return response()->json(['success' => false, 'message' => 'Curso de WordPress no enlazado en Laravel.'], 404);
            }

            $laravelCourse = $mapping->course; // El objeto Course de Laravel
            
            // Lógica para encontrar el módulo (asumimos el primer módulo si existe)
            // O, si Enrollment puede depender de 'course_id' directamente, mejor aún.
            // Voy a asumir que Enrollment PUEDE depender de 'course_id' directamente.
            
            $result = DB::transaction(function () use ($data, $isMinor, $laravelCourse) {

                // 3. Encontrar o Crear al Estudiante y Usuario
                // (Lógica copiada de tu EnrollmentController)
                $student = Student::where('cedula', $data['cedula'])->first();
                $user = User::where('email', $data['email'])->first();

                if ($student || $user) {
                    // Estudiante Existente
                    if ($student && $user && $student->user_id != $user->id) {
                         throw new \Exception('Conflicto de datos. La cédula y el email pertenecen a cuentas diferentes.');
                    }
                    $student = $student ?? $user->student;
                    if (!$student) {
                        throw new \Exception('Conflicto de usuario. Email existe pero no está enlazado a un estudiante.');
                    }
                    
                    Log::info("API WP->Laravel (V1): Estudiante encontrado (Cédula: {$data['cedula']})");

                } else {
                    // Estudiante Nuevo
                    Log::info("API WP->Laravel (V1): Creando nuevo estudiante (Cédula: {$data['cedula']})");
                    
                    // Crear Usuario (con acceso temporal)
                    $user = User::create([
                        'name' => $data['first_name'] . ' ' . $data['last_name'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['cedula']), // Cédula como contraseña inicial
                        'access_expires_at' => Carbon::now()->addMonths(3), // ACCESO TEMPORAL
                    ]);
                    // Asumimos que tienes 'spatie/laravel-permission' instalado por tu EnrollmentController
                    $user->assignRole('Estudiante');

                    // Crear Estudiante
                    $student = Student::create([
                        'user_id' => $user->id,
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'cedula' => $data['cedula'],
                        'email' => $data['email'],
                        'home_phone' => $data['phone'],
                        'mobile_phone' => $data['phone'], // Usamos 'phone' como fallback
                        'address' => $data['address'] ?? null,
                        'status' => 'Activo',
                        // 'balance' => 0, // Comentado (Columna no existe, según log anterior)
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
                }

                // 4. Crear la Inscripción (Enrollment) pendiente de pago
                // Esta es la diferencia clave: usamos 'course_id' en lugar de 'course_schedule_id'
                
                // Verificamos si ya tiene una inscripción PENDIENTE para este curso
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                                                ->where('course_id', $laravelCourse->id)
                                                ->where('status', 'Pendiente') // Asumiendo 'Pendiente'
                                                ->exists();

                if ($existingEnrollment) {
                    Log::warning("API WP->Laravel (V1): El estudiante ya tiene una inscripción pendiente para este curso.", $data);
                    return [
                        'status' => 'success',
                        'message' => 'Inscripción pendiente ya registrada.',
                        'student_id' => $student->id,
                    ];
                }

                // Creamos la nueva inscripción como "pendiente"
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $laravelCourse->id, // <-- Usamos el ID del CURSO
                    // 'course_schedule_id' => null, // Dejamos que el schedule se asigne después
                    'status' => 'Pendiente', // 'Pendiente' de pago
                    'enrollment_date' => now(),
                ]);

                // 5. Crear el Pago (Payment) pendiente
                // Asumimos que el precio está en el Módulo.
                // Si el curso tiene múltiples módulos, tomamos el primero.
                $module = $laravelCourse->modules->first(); // Asunción: el precio se basa en el primer módulo
                $amount = $module->price ?? 0;
                $payment_concept_id = $module->payment_concept_id ?? null;

                Payment::create([
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'payment_concept_id' => $payment_concept_id,
                    'amount' => $amount,
                    'currency' => 'DOP',
                    'status' => 'Pendiente',
                    'gateway' => 'Por Pagar',
                ]);
                
                Log::info("API WP->Laravel (V1): Nueva inscripción pendiente creada (ID: {$enrollment->id})");

                return [
                    'status' => 'success',
                    'message' => 'Inscripción procesada. Estudiante creado/actualizado.',
                    'student_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                ];
            });

            return response()->json($result, $result['status'] === 'success' ? 201 : 200);

        } catch (\Exception $e) {
            Log::critical("API WP->Laravel (V1): Error fatal al procesar inscripción.", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}