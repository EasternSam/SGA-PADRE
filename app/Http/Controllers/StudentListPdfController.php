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

        // 2. Obtener inscripciones
        $enrollments = Enrollment::with(['student', 'payment']) // Cargar 'payment' para ver la fecha
            ->where('course_schedule_id', $section->id)
            // CORRECCIÓN: Especificar la tabla 'enrollments' para evitar ambigüedad con 'students.status'
            ->whereNotIn('enrollments.status', ['Pendiente', 'pendiente']) 
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