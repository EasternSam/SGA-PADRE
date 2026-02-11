<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CurriculumPdfController extends Controller
{
    public function download(Course $career)
    {
        // Verificar que sea una carrera universitaria
        if ($career->program_type !== 'degree') {
            abort(404, 'Este curso no es una carrera universitaria.');
        }

        // Cargar relaciones necesarias: Módulos ordenados y sus prerrequisitos
        // El modelo Course ya ordena por 'period_number' y 'order' en la relación 'modules'
        $career->load(['modules.prerequisites']);

        // Agrupar módulos por periodo para facilitar la visualización en el PDF
        $modulesByPeriod = $career->modules->groupBy('period_number');

        $pdf = Pdf::loadView('reports.curriculum-pdf', [
            'career' => $career,
            'modulesByPeriod' => $modulesByPeriod,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        // Configuración del papel
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Pensum-' . $career->code . '.pdf');
    }
}