<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;

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
 * RUTA PÚBLICA PARA LA SINCRONIZACIÓN CON WORDPRESS
 * * Esta es la URL que recibirá los datos de las nuevas inscripciones
 * desde el plugin de WordPress (Fluent Forms).
 */
Route::post('/enroll', [EnrollmentController::class, 'store']);