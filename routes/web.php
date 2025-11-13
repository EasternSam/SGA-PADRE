<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController; 
use Illuminate\Support\Facades\Auth; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login'); // Redirige a login por defecto
});

// Ruta de 'dashboard' genérica que redirige según el rol
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        // --- ¡¡¡CORRECCIÓN!!! ---
        // Se usan los roles en español
        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Estudiante')) { // Cambiado de 'Student'
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('Profesor')) { // Cambiado de 'Teacher'
            return redirect()->route('teacher.dashboard');
        }
        
        // Fallback por si no tiene rol (esto causa que se quede en 'perfil')
        return redirect()->route('profile.edit');
    })->name('dashboard');

    // Perfil (Ruta genérica)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- RUTAS DE ADMINISTRADOR ---
// 'Admin' se queda igual
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('admin.dashboard');
    Route::get('/students', \App\Livewire\Students\Index::class)->name('admin.students.index');
    Route::get('/students/profile/{student}', \App\Livewire\StudentProfile\Index::class)->name('admin.students.profile'); // Cambiado studentId a student
    Route::get('/courses', \App\Livewire\Courses\Index::class)->name('admin.courses.index');
    Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');
    
    // --- RUTAS GESTIÓN DE PROFESORES (ACTUALIZADAS) ---
    // Ruta para la lista de profesores
    Route::get('/teachers', \App\Livewire\Teachers\Index::class)->name('admin.teachers.index');
    // Ruta para el perfil de un profesor (pasando el 'user' como 'teacher')
    Route::get('/teachers/profile/{teacher}', \App\Livewire\TeacherProfile\Index::class)->name('admin.teachers.profile');

    // Alias para que 'admin.profile.edit' apunte a la ruta de perfil genérica.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});

// --- RUTAS DE ESTUDIANTE ---
Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () { // Cambiado de 'Student' y añadido prefijo de nombre
    Route::get('/dashboard', \App\Livewire\StudentPortal\Dashboard::class)->name('dashboard');
    
    // --- ¡¡¡CORRECCIÓN DE RUTA!!! ---
    // Cambiamos el parámetro {enrollment} a {enrollmentId}
    // para que coincida con la variable del método mount()
    Route::get('/course/{enrollmentId}', \App\Livewire\StudentPortal\CourseDetail::class)->name('course.detail');
    
    // Alias para que 'student.profile.edit' apunte a la ruta de perfil genérica.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

// --- RUTAS DE PROFESOR ---
// --- ¡¡¡CORRECCIÓN DE PERMISOS!!! ---
// Se añade 'Admin' para que el administrador pueda ver las secciones de asistencia y notas
Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->group(function () { // Cambiado de 'Teacher'
    Route::get('/dashboard', \App\Livewire\TeacherPortal\Dashboard::class)->name('teacher.dashboard');
    // --- ¡CORRECCIÓN! ---
    // Las rutas de 'grades' y 'attendance' deben aceptar el ID de la sección
    Route::get('/grades/{section}', \App\Livewire\TeacherPortal\Grades::class)->name('teacher.grades');
    Route::get('/attendance/{section}', \App\Livewire\TeacherPortal\Attendance::class)->name('teacher.attendance');
    
    // Alias para que 'teacher.profile.edit' apunte a la ruta de perfil genérica.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('teacher.profile.edit');
});


// --- RUTAS DE REPORTES ---
Route::middleware(['auth'])->group(function () {
    // Asegurarse de que el parámetro coincida con el controlador (student)
    // --- ¡¡¡CORRECCIÓN!!! El método se llama 'generateStudentReport' en el controlador ---
    Route::get('/reports/student-report/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');

    // --- ¡¡¡RUTA AÑADIDA!!! ---
    // Ruta para el nuevo reporte de asistencia
    Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
});


// Rutas de autenticación
require __DIR__.'/auth.php';