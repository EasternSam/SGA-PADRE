<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentListPdfController extends Controller
{
    public function download(CourseSchedule $section)
    {
        // 1. Cargar relaciones necesarias
        $section->load(['module.course', 'teacher']);

        // 2. Obtener inscripciones (excluyendo pendientes de inscripción, 
        // pero incluimos aquellos que deben pago si están inscritos)
        $enrollments = Enrollment::with(['student', 'payment']) // Cargar 'payment' para ver la fecha
            ->where('course_schedule_id', $section->id)
            ->whereNotIn('status', ['Pendiente', 'pendiente']) // Ajusta según tu lógica de "inscrito"
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('enrollments.*') 
            ->get();

        $data = [
            'schedule' => $section,
            'enrollments' => $enrollments,
        ];

        // 3. Generar PDF
        $pdf = Pdf::loadView('reports.student-list-report-pdf', ['data' => $data]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Nomina_Estudiantes_' . $section->section_name . '.pdf');
    }
}