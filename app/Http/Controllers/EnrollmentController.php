<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module; // <-- Importar Module
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    /**
     * Maneja la recepción de una nueva inscripción desde WordPress (Fluent Forms).
     * Esta es la lógica principal de sincronización.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validamos los datos
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cedula' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'is_minor_flag' => 'nullable|string',
            'course_name' => 'required|string|max:255', // Esto ahora será el nombre del MÓDULO
            'schedule_string' => 'required|string|max:255', // Esto ahora será la SECCIÓN
            
            // Campos de estudiante
            'city' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'how_found' => 'nullable|string|max:100',
            'mobile_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos de entrada inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // 2. Lógica de Menores (Regla 4 del plugin)
        $isMinor = !empty($data['is_minor_flag']) && $data['is_minor_flag'] !== 'No soy menor';
        $cedulaToStore = $data['cedula'];
        $cedulaToSearch = $data['cedula'];

        if ($isMinor) {
            $cedulaToStore = DB::transaction(function () use ($data) {
                $optionKey = 'tutor_counter_' . $data['cedula'];
                $counterRow = DB::table('system_options')->where('key', $optionKey)->lockForUpdate()->first();
                $newCounter = 1;
                if ($counterRow) {
                    $newCounter = (int)$counterRow->value + 1;
                    DB::table('system_options')->where('key', $optionKey)->update(['value' => $newCounter]);
                } else {
                    DB::table('system_options')->insert(['key' => $optionKey, 'value' => $newCounter]);
                }
                return $data['cedula'] . '-' . $newCounter;
            });
        }

        // 3. Iniciar una transacción de base de datos
        try {
            $result = DB::transaction(function () use ($data, $cedulaToSearch, $cedulaToStore, $isMinor) {
                
                // 4. Buscar o Crear el Estudiante (Student)
                // (Esta lógica no cambia)
                $student = null;
                if (!$isMinor) {
                    $student = Student::where('cedula', $cedulaToSearch)->first();
                }

                $studentData = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'cedula' => $cedulaToStore,
                    'email' => $data['email'],
                    'home_phone' => $data['phone'],
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'sector' => $data['sector'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'how_found' => $data['how_found'] ?? null,
                    'is_minor' => $isMinor,
                    'mobile_phone' => $data['mobile_phone'] ?? $data['phone'],
                    'status' => 'Activa',
                ];

                if ($student) {
                    unset($studentData['first_name'], $studentData['last_name'], $studentData['cedula']);
                    $student->update($studentData);
                } else {
                    $student = Student::create($studentData);
                }

                // --- 5. LÓGICA ACADÉMICA ACTUALIZADA ---

                // La API de WP no envía el "Curso Padre" (ej. Informática),
                // solo el módulo (ej. Excel). Crearemos un curso padre "General"
                // para agrupar todos los módulos que lleguen por la API.
                
                // 5a. Buscar o Crear el Curso Padre (Course)
                $courseParent = Course::firstOrCreate(
                    ['name' => 'Cursos Generales (API)'],
                    ['description' => 'Categoría general para módulos creados por la API.', 'status' => 'Activo']
                );

                // 5b. Buscar o Crear el Módulo (Module)
                // Asumimos que 'course_name' de la API es el nombre del MÓDULO.
                $module = Module::firstOrCreate(
                    [
                        'course_id' => $courseParent->id,
                        'name' => $data['course_name']
                    ],
                    [
                        'code' => 'API-' . Str::slug($data['course_name']),
                        'price' => 0, // La API no envía precio
                        'status' => 'Activo'
                    ]
                );
                
                // 5c. Buscar o Crear la Sección (CourseSchedule)
                // La API no envía días/horas separados. Guardamos el string en 'day_of_week'.
                $schedule = CourseSchedule::firstOrCreate(
                    [
                        'module_id' => $module->id,
                        'day_of_week' => $data['schedule_string'], // "Lunes 6-8pm"
                    ],
                    [
                        'start_time' => '00:00:00', // No tenemos esta data
                        'end_time' => '00:00:00',   // No tenemos esta data
                        'status' => 'Abierta'
                    ]
                );

                // 6. Manejar la Inscripción (Enrollment)
                // Buscamos si el estudiante ya está en esta SECCIÓN.
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('course_schedule_id', $schedule->id)
                    ->first();

                if ($existingEnrollment) {
                    // Regla 1: Duplicado exacto. Descartar.
                    return ['status' => 'duplicate', 'message' => 'Inscripción duplicada descartada.'];
                }

                // Regla 2 (Modificada): Si el estudiante ya está en otro horario
                // del MISMO MÓDULO, ¿lo actualizamos o lo inscribimos en ambos?
                // Por ahora, la lógica es inscribirlo, ya que el duplicado exacto ya se filtró.
                
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_schedule_id' => $schedule->id,
                    'status' => 'Cursando',
                    // 'wp_enrollment_key' => $student->id . ':' . $schedule->id . ':' . Str::random(4) // Opcional
                ]);
                $logMessage = 'Nueva inscripción creada en Laravel.';

                return [
                    'status' => 'success',
                    'message' => $logMessage,
                    'student_id_laravel' => $student->id,
                    'enrollment_id_laravel' => $enrollment->id,
                    // 'wp_enrollment_key' => $enrollment->wp_enrollment_key
                ];

            }); // Fin de la transacción

            return response()->json($result, $result['status'] == 'duplicate' ? 200 : 201);

        } catch (\Exception $e) {
            // Si algo falla en la transacción, se revierte todo
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor al procesar la inscripción.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}