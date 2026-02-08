<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class GradesPdfController extends Controller
{
    public function download(CourseSchedule $section)
    {
        $user = Auth::user();

        // 1. SEGURIDAD: Jerarquía de Permisos
        // CASO A: Roles Administrativos (Acceso Total)
        if ($user->hasAnyRole(['Admin', 'Registro', 'Dirección'])) {
            // Acceso concedido automáticamente
        }
        // CASO B: Profesores (Solo sus secciones)
        elseif ($user->hasRole('Profesor')) {
            if ($section->teacher_id !== $user->id) {
                abort(403, 'No tienes permiso para descargar el reporte de esta sección.');
            }
        }
        // CASO C: Otros (Estudiantes, invitados) -> Bloqueado
        else {
            abort(403, 'No tienes permisos para acceder a reportes de calificaciones generales.');
        }

        // 2. Cargar relaciones
        $section->load(['module.course', 'teacher']);

        // 3. Obtener inscripciones
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $section->id)
            ->whereNotIn('enrollments.status', ['Pendiente', 'pendiente'])
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('enrollments.*')
            ->get();

        // 4. Calcular Estadísticas
        $stats = [
            'total' => $enrollments->count(),
            'aprobados' => $enrollments->where('final_grade', '>=', 70)->count(),
            'reprobados' => $enrollments->where('final_grade', '<', 70)->whereNotNull('final_grade')->count(),
            'promedio' => $enrollments->avg('final_grade')
        ];

        $data = [
            'schedule' => $section,
            'enrollments' => $enrollments,
            'stats' => $stats
        ];

        $pdf = Pdf::loadView('reports.grades-report-pdf', ['data' => $data]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Reporte_Calificaciones_' . $section->section_name . '.pdf');
    }
}