<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class GradesPdfController extends Controller
{
    public function download(CourseSchedule $section)
    {
        // 1. Cargar relaciones necesarias
        $section->load(['module.course', 'teacher']);

        // 2. Obtener inscripciones (excluyendo pendientes)
        // Usamos la misma lógica de ordenamiento que en la vista web
        $enrollments = Enrollment::with('student')
            ->where('course_schedule_id', $section->id)
            // CORRECCIÓN: Especificar la tabla 'enrollments' para evitar ambigüedad
            ->whereNotIn('enrollments.status', ['Pendiente', 'pendiente'])
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('enrollments.*') // Asegurar que traemos campos de enrollment
            ->get();

        $data = [
            'schedule' => $section,
            'enrollments' => $enrollments,
        ];

        // 3. Generar PDF usando la vista creada 'reports.grades-report-pdf'
        // Asegúrate de que el archivo resources/views/reports/grades-report-pdf.blade.php exista.
        $pdf = Pdf::loadView('reports.grades-report-pdf', ['data' => $data]);

        $pdf->setPaper('a4', 'portrait'); // Vertical suele ser mejor para notas

        return $pdf->stream('Reporte_Calificaciones_' . $section->section_name . '.pdf');
    }
}