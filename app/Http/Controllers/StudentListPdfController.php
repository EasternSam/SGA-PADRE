<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StudentListPdfController extends Controller
{
    public function download(CourseSchedule $section)
    {
        // 1. SEGURIDAD (IDOR Protection)
        $user = Auth::user();
        if ($user->hasRole('Profesor') && $section->teacher_id !== $user->id) {
            abort(403, 'No tienes permiso para generar la lista de esta sección.');
        }

        $section->load(['module.course', 'teacher']);

        $enrollments = Enrollment::with(['student', 'payment'])
            ->where('course_schedule_id', $section->id)
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

        $pdf = Pdf::loadView('reports.student-list-report-pdf', ['data' => $data]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Nomina_Estudiantes_' . $section->section_name . '.pdf');
    }
}