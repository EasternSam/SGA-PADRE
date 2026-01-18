<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class CertificatePdfController extends Controller
{
    /**
     * Generar y descargar el certificado en PDF con QR de validación.
     */
    public function download(Student $student, Course $course)
    {
        // 1. Generar URL firmada (Signed URL)
        // Esta URL es única para este estudiante y curso, y no puede ser falsificada.
        $validationUrl = URL::signedRoute(
            'certificates.verify',
            ['student' => $student->id, 'course' => $course->id]
        );

        // 2. Generar URL de imagen QR
        // Usamos una API pública rápida para generar el QR que DomPDF pueda incrustar.
        // size=150x150 asegura buena resolución para el PDF.
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($validationUrl);

        // 3. Generar Folio Único
        // Formato: CERT-INICIALES-AÑO-ID_ESTUDIANTE-ID_CURSO
        $folio = 'CERT-' . 
                 strtoupper(substr($student->last_name, 0, 2)) . 
                 date('Y') . '-' . 
                 str_pad($student->id, 4, '0', STR_PAD_LEFT) . 
                 str_pad($course->id, 4, '0', STR_PAD_LEFT);

        $data = [
            'student' => $student,
            'course' => $course,
            'date' => Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'director_name' => 'Dirección Académica', // Puedes cambiar esto o traerlo de una config
            'institution_name' => 'SGA PADRE',
            'validation_url' => $validationUrl,
            'qr_code_url' => $qrCodeUrl,
            'folio' => $folio
        ];

        // 4. Generar PDF
        // 'isRemoteEnabled' => true es CRÍTICO para descargar la imagen del QR de la API externa
        $pdf = Pdf::loadView('reports.certificate-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream('Diploma_' . $folio . '.pdf');
    }

    /**
     * Vista de validación pública (Al escanear el QR).
     */
    public function verify(Request $request, Student $student, Course $course)
    {
        // Si el middleware 'signed' pasa, significa que la URL es auténtica.
        
        return view('reports.certificate-validation', [
            'student' => $student,
            'course' => $course,
            'verified_at' => now(),
            'isValid' => true
        ]);
    }
}