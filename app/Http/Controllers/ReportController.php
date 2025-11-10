<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
// use Illuminate\View\View; // <-- Ya no se usa
use Illuminate\Http\Response; // <-- ¡NUEVO! Se usará para el PDF
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // <-- ¡NUEVO! Añadir el facade de PDF

class ReportController extends Controller
{
    /**
     * Muestra el reporte de un estudiante.
     *
     * @param Student $student
     * @return \Illuminate\Http\Response // <-- ¡CORREGIDO! Tipo de retorno
     */
    public function studentReport(Student $student): Response // <-- ¡CORREGIDO!
    {
        try {
            // Cargar todas las relaciones necesarias para el reporte
            $student->load([
                'user',
                'enrollments.courseSchedule.module.course', 
                'enrollments.courseSchedule.teacher',
                'payments.concept',
                'payments.user',
                'payments.enrollment.courseSchedule.module.course' 
            ]);

            // --- ¡¡¡CORRECCIÓN!!! ---
            // La vista 'reports.student-report.blade.php' espera una variable '$enrollments'.
            $enrollments = $student->enrollments;

            // Generar el PDF usando el facade
            $pdf = Pdf::loadView('reports.student-report', [
                'student' => $student,
                'enrollments' => $enrollments // <-- ¡NUEVO! Pasar la variable a la vista
            ]);
            
            // Devolver el PDF para que se vea en el navegador (stream)
            // Esto abrirá el PDF en la pestaña, no lo descargará.
            return $pdf->stream('reporte-'.$student->cedula.'.pdf');

        } catch (\Exception $e) {
            Log::error("Error al generar reporte para estudiante ID {$student->id}: " . $e->getMessage(). "\n" . $e->getTraceAsString());
            
            // Devolver una respuesta de error simple
            return response('Error al generar el reporte. Revise el log.', 500);
        }
    }
}