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
use App\Models\CallLog;

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
                // MODIFICACIÓN: Normalizamos la cédula para búsquedas y para la contraseña
                $cleanCedula = preg_replace('/[^0-9]/', '', $data['cedula']);
                $student = Student::where('cedula', $data['cedula'])->orWhere('cedula', $cleanCedula)->first();
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
                        'password' => Hash::make($cleanCedula), // MODIFICADO: Guardamos la contraseña sin guiones para evitar errores en login
                        'access_expires_at' => Carbon::now()->addMonths(3), 
                        'email_verified_at' => now(), // AÑADIDO: Marcamos verificado para saltar middleware verified
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

                // --- INJECTION: GOD TIER ACCOUNTING ENGINE ---
                try {
                    app(\App\Services\AccountingEngine::class)->registerStudentDebt($enrollment, $amount);
                } catch (\Exception $e) {
                    Log::error("Accounting Engine Error on WP Enrollment: " . $e->getMessage());
                }

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

    /**
     * Sincroniza un registro de llamada enviado desde WordPress a Laravel.
     */
    public function syncCallLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wp_call_id'   => 'required|integer',
            'cedula'       => 'required|string',
            'course_name'  => 'required|string',
            'agent_email'  => 'nullable|email',
            'comments'     => 'nullable|string',
            'status'       => 'required|string',
            'created_at'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            // Buscar estudiante
            $student = Student::where('cedula', $data['cedula'])->first();
            if (!$student) {
                // Fallback: buscar por cédula normalizada
                $cleanCedula = preg_replace('/[^0-9]/', '', $data['cedula']);
                $student = Student::where('cedula', $cleanCedula)->first();
            }

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Estudiante no encontrado en Laravel.'], 404);
            }

            // Buscar agente
            $agent = null;
            if (!empty($data['agent_email'])) {
                $agent = User::where('email', $data['agent_email'])->first();
            }
            $agent_id = $agent ? $agent->id : auth()->id();

            // Buscar inscripción (enrollment) por estudiante y curso
            $enrollment = Enrollment::where('student_id', $student->id)
                ->whereHas('course', function ($query) use ($data) {
                    $query->where('name', 'LIKE', '%' . $data['course_name'] . '%');
                })
                ->latest()
                ->first();

            // Sincronizar (crear o actualizar) el CallLog
            $callLog = CallLog::updateOrCreate(
                ['wp_call_id' => $data['wp_call_id']],
                [
                    'student_id'    => $student->id,
                    'enrollment_id' => $enrollment ? $enrollment->id : null,
                    'agent_id'      => $agent_id ?? 1, // Fallback a user ID 1
                    'comment'       => $data['comments'] ?? '',
                    'status'        => $data['status'],
                    'created_at'    => !empty($data['created_at']) ? Carbon::parse($data['created_at']) : now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Registro de llamada sincronizado correctamente.',
                'call_log_id' => $callLog->id
            ], 200);

        } catch (\Exception $e) {
            Log::error("API WP->Laravel (V1): Error al sincronizar llamada.", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene el listado de pagos paginado desde Laravel.
     */
    public function getPayments(Request $request)
    {
        try {
            $search = $request->input('search');
            
            $query = Payment::with(['student', 'paymentConcept', 'enrollment.course']);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('student', function ($sq) use ($search) {
                        $sq->where('first_name', 'LIKE', "%{$search}%")
                           ->orWhere('last_name', 'LIKE', "%{$search}%")
                           ->orWhere('cedula', 'LIKE', "%{$search}%")
                           ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('gateway', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhereHas('paymentConcept', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%");
                    });
                });
            }

            $payments = $query->orderBy('due_date', 'desc')->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
            ], 200);
        } catch (\Exception $e) {
            Log::error("API WP->Laravel (V1): Error al obtener pagos.", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza el perfil de estudiante en Laravel.
     */
    public function updateStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula'             => 'required|string',
            'first_name'         => 'nullable|string|max:255',
            'last_name'          => 'nullable|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:20',
            'mobile_phone'       => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'city'               => 'nullable|string|max:255',
            'sector'             => 'nullable|string|max:255',
            'tutor_name'         => 'nullable|string|max:255',
            'tutor_cedula'       => 'nullable|string|max:255',
            'tutor_phone'        => 'nullable|string|max:255',
            'tutor_relationship' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $student = Student::where('cedula', $data['cedula'])->first();
            if (!$student) {
                // Fallback
                $cleanCedula = preg_replace('/[^0-9]/', '', $data['cedula']);
                $student = Student::where('cedula', $cleanCedula)->first();
            }

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Estudiante no encontrado en Laravel.'], 404);
            }

            $updateData = [];
            if (isset($data['first_name'])) $updateData['first_name'] = $data['first_name'];
            if (isset($data['last_name'])) $updateData['last_name'] = $data['last_name'];
            if (isset($data['email'])) $updateData['email'] = $data['email'];
            if (isset($data['phone'])) $updateData['home_phone'] = $data['phone'];
            if (isset($data['mobile_phone'])) $updateData['mobile_phone'] = $data['mobile_phone'];
            if (isset($data['address'])) $updateData['address'] = $data['address'];
            if (isset($data['city'])) $updateData['city'] = $data['city'];
            if (isset($data['sector'])) $updateData['sector'] = $data['sector'];
            if (isset($data['tutor_name'])) $updateData['tutor_name'] = $data['tutor_name'];
            if (isset($data['tutor_cedula'])) $updateData['tutor_cedula'] = $data['tutor_cedula'];
            if (isset($data['tutor_phone'])) $updateData['tutor_phone'] = $data['tutor_phone'];
            if (isset($data['tutor_relationship'])) $updateData['tutor_relationship'] = $data['tutor_relationship'];

            $student->update($updateData);

            $user = $student->user;
            if ($user) {
                $userData = [];
                if (isset($data['first_name']) || isset($data['last_name'])) {
                    $userData['name'] = ($data['first_name'] ?? $student->first_name) . ' ' . ($data['last_name'] ?? $student->last_name);
                }
                if (isset($data['email'])) {
                    $userData['email'] = $data['email'];
                }
                if (!empty($userData)) {
                    $user->update($userData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos del estudiante actualizados en Laravel correctamente.'
            ], 200);

        } catch (\Exception $e) {
            Log::error("API WP->Laravel (V1): Error al actualizar estudiante.", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene estadísticas unificadas del centro educativo desde Laravel.
     */
    public function getDashboardStats()
    {
        try {
            $totalStudents = Student::count();
            $totalCourses = Course::where('status', 'Activo')->count();
            if ($totalCourses === 0) {
                $totalCourses = Course::count();
            }
            $pendingEnrollments = Enrollment::where('status', 'Pendiente')->count();
            $pendingPayments = Payment::whereIn('status', ['Pendiente', 'unpaid', 'Por Pagar'])->count();
            $paidPaymentsSum = Payment::whereIn('status', ['paid', 'Completado', 'Pagado'])->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_students'      => $totalStudents,
                    'total_courses'       => $totalCourses,
                    'pending_enrollments' => $pendingEnrollments,
                    'pending_payments'    => $pendingPayments,
                    'total_revenue'       => (float) $paidPaymentsSum,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error("API WP->Laravel (V1): Error al obtener estadísticas del dashboard.", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}