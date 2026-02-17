<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Routing\Controller; 
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
use App\Models\Payment; 
use App\Models\ScheduleMapping;
use App\Models\CourseSchedule;
use App\Models\PaymentConcept; 

class WordpressIntegrationController extends Controller
{
    /**
     * Maneja la solicitud de una nueva inscripción desde WordPress (Fluent Forms).
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
            'wp_course_id' => 'required|integer', 
            'wp_schedule_string' => 'required|string|max:255', 

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
            // 2. Encontrar el curso de Laravel usando el Mapeo
            $mapping = CourseMapping::with('course.modules')->where('wp_course_id', $data['wp_course_id'])->first();

            if (!$mapping) {
                Log::error("API WP->Laravel (V1): No se encontró mapeo para el wp_course_id: {$data['wp_course_id']}");
                return response()->json(['success' => false, 'message' => 'Curso de WordPress no enlazado en Laravel.'], 404);
            }

            $laravelCourse = $mapping->course; 
            
            // Buscar la sección (schedule) usando el mapeo
            $scheduleMapping = ScheduleMapping::where('wp_course_id', $data['wp_course_id'])
                                              ->where('wp_schedule_string', $data['wp_schedule_string'])
                                              ->first();

            if (!$scheduleMapping) {
                Log::error("API WP->Laravel (V1): No se encontró mapeo de SECCIÓN.", [
                    'wp_course_id' => $data['wp_course_id'],
                    'wp_schedule_string' => $data['wp_schedule_string']
                ]);
                return response()->json(['success' => false, 'message' => 'Sección (horario) de WordPress no enlazada en Laravel.'], 404);
            }
            
            $laravel_schedule_id = $scheduleMapping->course_schedule_id;
            
            $result = DB::transaction(function () use ($data, $isMinor, $laravelCourse, $laravel_schedule_id) {

                // 3. Encontrar o Crear al Estudiante y Usuario
                $student = Student::where('cedula', $data['cedula'])->first();
                $user = User::where('email', $data['email'])->first();

                if ($student) {
                    // Caso A: El estudiante ya existe por cédula
                    Log::info("API WP->Laravel (V1): Estudiante encontrado por Cédula ({$data['cedula']})");
                    
                    // Verificación de consistencia (opcional)
                    if ($user && $student->user_id != $user->id) {
                        Log::warning("API WP: Conflicto potencial. Cédula {$data['cedula']} pertenece al usuario {$student->user_id}, pero el email {$data['email']} es del usuario {$user->id}. Se usará el estudiante encontrado por cédula.");
                    }
                } elseif ($user) {
                    // Caso B: El usuario existe por email, pero no encontramos estudiante por cédula
                    Log::info("API WP->Laravel (V1): Usuario encontrado por Email ({$data['email']}). Buscando perfil de estudiante...");
                    
                    $student = $user->student;

                    if (!$student) {
                        // FIX CRÍTICO: Si el usuario existe pero no tiene perfil de estudiante, LO CREAMOS ahora.
                        Log::info("API WP->Laravel (V1): Usuario existe sin perfil de estudiante. Creando perfil...");
                        
                        $student = Student::create([
                            'user_id' => $user->id,
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'cedula' => $data['cedula'],
                            'email' => $data['email'], // Aseguramos que coincida
                            'home_phone' => $data['phone'],
                            'mobile_phone' => $data['phone'], 
                            'address' => $data['address'] ?? null,
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
                        
                        // Aseguramos que tenga el rol de estudiante
                        if (!$user->hasRole('Estudiante')) {
                            $user->assignRole('Estudiante');
                        }
                    } else {
                        // El usuario tiene estudiante, pero la cédula no coincidió en la búsqueda inicial
                        if ($student->cedula !== $data['cedula']) {
                             Log::warning("API WP->Laravel (V1): Mismatch de cédula para el usuario {$user->email}. Registrada: {$student->cedula}, Nueva: {$data['cedula']}. Se usará el perfil existente.");
                        }
                    }

                } else {
                    // Caso C: Ni estudiante ni usuario existen. Crear todo nuevo.
                    Log::info("API WP->Laravel (V1): Creando nuevo usuario y estudiante (Cédula: {$data['cedula']})");
                    
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
                        'mobile_phone' => $data['phone'], 
                        'address' => $data['address'] ?? null,
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
                }

                // 4. Verificar inscripción pendiente
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                                                ->where('course_schedule_id', $laravel_schedule_id)
                                                ->where('status', 'Pendiente')
                                                ->exists();

                if ($existingEnrollment) {
                    return [
                        'status' => 'success',
                        'message' => 'Inscripción pendiente ya registrada.',
                        'student_id' => $student->id,
                    ];
                }

                // Crear Inscripción
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $laravelCourse->id,
                    'course_schedule_id' => $laravel_schedule_id,
                    'status' => 'Pendiente',
                    'enrollment_date' => now(),
                ]);

                // 5. Crear el Pago (Payment)
                $inscriptionConcept = PaymentConcept::firstOrCreate(
                    ['name' => 'Inscripción'],
                    ['description' => 'Pago único de inscripción al curso']
                );

                $amount = $laravelCourse->registration_fee ?? 0;

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
                
                Log::info("API WP->Laravel (V1): Nueva inscripción creada con cargo de Inscripción (Monto: $amount)");

                return [
                    'status' => 'success',
                    'message' => 'Inscripción procesada. Cargo de inscripción generado.',
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