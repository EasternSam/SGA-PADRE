<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\SchoolConfig;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentGrade;
use Barryvdh\DomPDF\Facade\Pdf;

class GradeReportPdfController extends Controller
{
    /**
     * Reporte académico de una sección por período.
     */
    public function sectionGrades(Section $section, EvaluationPeriod $period)
    {
        $section->load('gradeLevel');
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();

        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $sectionSubjects = SectionSubject::where('section_id', $section->id)
            ->with('subject')
            ->get();

        // Build grade matrix
        $grades = StudentGrade::where('evaluation_period_id', $period->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $matrix = [];
        $subjectAverages = [];

        foreach ($students as $student) {
            $studentGrades = $grades[$student->id] ?? collect();
            $row = [];
            $studentTotal = 0;
            $studentCount = 0;

            foreach ($sectionSubjects as $ss) {
                $grade = $studentGrades->firstWhere('section_subject_id', $ss->id);
                $score = $grade?->score;
                $row[$ss->id] = $score;

                if ($score !== null) {
                    $studentTotal += $score;
                    $studentCount++;
                    $subjectAverages[$ss->id] = ($subjectAverages[$ss->id] ?? ['total' => 0, 'count' => 0]);
                    $subjectAverages[$ss->id]['total'] += $score;
                    $subjectAverages[$ss->id]['count']++;
                }
            }

            $matrix[$student->id] = [
                'grades'  => $row,
                'average' => $studentCount > 0 ? round($studentTotal / $studentCount, 1) : null,
            ];
        }

        // Calculate subject averages
        foreach ($subjectAverages as $ssId => &$sa) {
            $sa['avg'] = $sa['count'] > 0 ? round($sa['total'] / $sa['count'], 1) : null;
        }

        $data = compact('section', 'period', 'students', 'sectionSubjects', 'matrix', 'subjectAverages', 'schoolConfig', 'activeYear');

        $pdf = Pdf::loadView('reports.grade-report-section-pdf', $data);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('Calificaciones_' . $section->gradeLevel?->short_name . '_' . $section->name . '_' . $period->name . '.pdf');
    }
}
