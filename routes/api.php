<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController; // <-- Tu import original
use App\Models\Course; // <-- Import para el ejemplo de cursos

// ====================================================================
// IMPORT DEL NUEVO CONTROLADOR (PUNTO 1)
// ====================================================================
use App\Http\Controllers\Api\V1\WordpressIntegrationController;
// ====================================================================


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Ruta pública para recibir inscripciones desde la página web (Fluent Forms, etc.)
 * ESTA RUTA ES PÚBLICA Y NO REQUIERE TOKEN
 */
Route::post('/enroll', [EnrollmentController::class, 'store']);


// --- RUTAS PROTEGIDAS PARA EL PLUGIN DE WORDPRESS ---

// Definimos un grupo de rutas para nuestra API v1
// Todas las rutas aquí dentro requerirán autenticación de Sanctum (Bearer Token)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    /**
     * Endpoint de prueba
     * Para verificar que la conexión y autenticación funcionan.
     * URL: /api/v1/test
     */
    Route::get('/test', function () {
        return response()->json([
            'status' => 'success',
            'message' => '¡Conexión API exitosa desde WordPress a Laravel!',
        ]);
    });

    /**
     * Endpoint de ejemplo para OBTENER CURSOS
     * URL: /api/v1/courses
     */
    Route::get('/courses', function () {
        try {
            // Usamos el modelo Course que ya existe en tu app
            $courses = Course::all(); 
            
            return response()->json([
                'status' => 'success',
                'data' => $courses,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener cursos: ' . $e->getMessage()
            ], 500);
        }
    });

    // ====================================================================
    // NUEVO ENDPOINT PARA RECIBIR INSCRIPCIONES (PUNTO 1)
    // ====================================================================
    /**
     * Endpoint para recibir nuevas inscripciones desde Fluent Forms (WP).
     * Este SÍ usa el CourseMapping (Punto 3).
     * URL: /api/v1/wordpress/new-inscription
     */
    Route::post('/wordpress/new-inscription', [WordpressIntegrationController::class, 'handleNewInscription']);
    // ====================================================================


    /**
     * Endpoint de ejemplo para OBTENER ESTUDIANTES
     * URL: /api/v1/students
     */
    // Route::get('/students', function () {
    //     $students = \App\Models\Student::all();
    //     return response()->json(['status' => 'success', 'data' => $students]);
    // });
    
    /**
     * Endpoint de ejemplo para OBTENER UN ESTUDIANTE
     * URL: /api/v1/student/123
     */
    // Route::get('/student/{id}', function ($id) {
    //     $student = \App\Models\Student::find($id);
    //     if (!$student) {
    //         return response()->json(['status' => 'error', 'message' => 'Estudiante no encontrado'], 404);
    //     }
    //     return response()->json(['status' => 'success', 'data' => $student]);
    // });


    // --- Puedes añadir todos tus endpoints (GET, POST, PUT, DELETE) aquí ---

});