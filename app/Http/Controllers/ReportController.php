<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // <-- ¡Importante! Asegúrate de que esto esté.

class ReportController extends Controller
{
    /**
     * Genera un reporte de estudiante.
     *
     * @param Student $student
     * @return \Illuminate\Http\Response
     */
    public function generateStudentReport(Student $student)
    {
        // Cargar las relaciones necesarias para el reporte
        $student->load(
            'enrollments.courseSchedule.module.course', 
            'enrollments.courseSchedule.teacher', 
            'payments.paymentConcept'
        );

        $data = [
            'student' => $student,
            'enrollments' => $student->enrollments,
            'payments' => $student->payments,
        ];

        // --- ¡ESTA ES LA CORRECCIÓN! ---
        
        // 1. Cargamos la misma vista que ya tenías ('reports.student-report')
        //    y le pasamos los datos.
        $pdf = Pdf::loadView('reports.student-report', $data);

        // 2. En lugar de descargar, usamos ->stream() para que se muestre 
        //    en el navegador (perfecto para nuestro <iframe> en el modal).
        return $pdf->stream('reporte-estudiante-' . $student->id . '.pdf');
    }
}