<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CertificatePdfController extends Controller
{
    /**
     * Generar y descargar el certificado en PDF.
     */
    public function download(Student $student, Course $course)
    {
        // Validar que el estudiante esté inscrito en el curso (Opcional, seguridad extra)
        // if (!$student->courses->contains($course->id)) { abort(403); }

        $data = [
            'student' => $student,
            'course' => $course,
            'date' => Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'director_name' => 'Dirección Académica', // Puedes hacerlo dinámico desde SystemOptions
            'institution_name' => 'SGA PADRE', // O el nombre de tu institución
        ];

        // Configurar el PDF en horizontal (landscape)
        $pdf = Pdf::loadView('reports.certificate-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Certificado_' . $student->id . '_' . $course->id . '.pdf');
    }
}