<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\StudentAttendance;
use App\Models\EvaluationPeriod;
use App\Models\AcademicYear;
use App\Models\SchoolConfig;
use App\Models\ReportCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCardPdfController extends Controller
{
    /**
     * Generate individual student report card PDF.
     */
    public function download(Student $student, EvaluationPeriod $period)
    {
        $activeYear = $period->academicYear;
        $section = Section::with('gradeLevel')->find($student->section_id);

        if (!$section) {
            abort(404, 'El estudiante no tiene sección asignada.');
        }

        // Get all subjects for this section
        $sectionSubjects = $section->sectionSubjects()
            ->with('subject')
            ->orderBy('id')
            ->get();

        // Get grades for this student & period
        $grades = StudentGrade::where('student_id', $student->id)
            ->where('evaluation_period_id', $period->id)
            ->pluck('score', 'section_subject_id');

        // Get attendance stats
        $periodStart = $period->start_date;
        $periodEnd = $period->end_date;

        $attendanceStats = [
            'total_days' => StudentAttendance::where('student_id', $student->id)
                ->whereBetween('date', [$periodStart, $periodEnd])->count(),
            'present' => StudentAttendance::where('student_id', $student->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->where('status', 'present')->count(),
            'absent' => StudentAttendance::where('student_id', $student->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->where('status', 'absent')->count(),
            'late' => StudentAttendance::where('student_id', $student->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->where('status', 'late')->count(),
        ];

        // Get report card notes if they exist
        $reportCard = ReportCard::where('student_id', $student->id)
            ->where('evaluation_period_id', $period->id)
            ->first();

        $schoolConfig = SchoolConfig::current();

        // All 4 periods for cumulative view
        $allPeriods = EvaluationPeriod::where('academic_year_id', $activeYear->id)
            ->orderBy('number')->get();

        $allGrades = [];
        foreach ($allPeriods as $p) {
            $allGrades[$p->id] = StudentGrade::where('student_id', $student->id)
                ->where('evaluation_period_id', $p->id)
                ->pluck('score', 'section_subject_id');
        }

        $data = compact(
            'student', 'section', 'period', 'activeYear',
            'sectionSubjects', 'grades', 'attendanceStats',
            'reportCard', 'schoolConfig', 'allPeriods', 'allGrades'
        );

        $pdf = Pdf::loadView('reports.report-card-pdf', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = 'Boletin_' . $student->last_name . '_' . $student->first_name . '_P' . $period->number . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Generate report cards for entire section (batch).
     */
    public function downloadSection(Section $section, EvaluationPeriod $period)
    {
        $section->load('gradeLevel');
        $students = Student::where('section_id', $section->id)
            ->where('status', 'Activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $sectionSubjects = $section->sectionSubjects()
            ->with('subject')
            ->orderBy('id')
            ->get();

        $allPeriods = EvaluationPeriod::where('academic_year_id', $period->academic_year_id)
            ->orderBy('number')->get();

        $schoolConfig = SchoolConfig::current();
        $activeYear = $period->academicYear;

        // Collect all data for each student
        $studentsData = [];
        foreach ($students as $student) {
            $grades = StudentGrade::where('student_id', $student->id)
                ->where('evaluation_period_id', $period->id)
                ->pluck('score', 'section_subject_id');

            $periodStart = $period->start_date;
            $periodEnd = $period->end_date;

            $attendanceStats = [
                'total_days' => StudentAttendance::where('student_id', $student->id)
                    ->whereBetween('date', [$periodStart, $periodEnd])->count(),
                'present' => StudentAttendance::where('student_id', $student->id)
                    ->whereBetween('date', [$periodStart, $periodEnd])
                    ->where('status', 'present')->count(),
                'absent' => StudentAttendance::where('student_id', $student->id)
                    ->whereBetween('date', [$periodStart, $periodEnd])
                    ->where('status', 'absent')->count(),
                'late' => StudentAttendance::where('student_id', $student->id)
                    ->whereBetween('date', [$periodStart, $periodEnd])
                    ->where('status', 'late')->count(),
            ];

            $reportCard = ReportCard::where('student_id', $student->id)
                ->where('evaluation_period_id', $period->id)
                ->first();

            $allGrades = [];
            foreach ($allPeriods as $p) {
                $allGrades[$p->id] = StudentGrade::where('student_id', $student->id)
                    ->where('evaluation_period_id', $p->id)
                    ->pluck('score', 'section_subject_id');
            }

            $studentsData[] = compact('student', 'grades', 'attendanceStats', 'reportCard', 'allGrades');
        }

        $data = compact(
            'section', 'period', 'activeYear', 'sectionSubjects',
            'schoolConfig', 'allPeriods', 'studentsData'
        );

        $pdf = Pdf::loadView('reports.report-cards-batch-pdf', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = 'Boletines_' . $section->gradeLevel?->short_name . '_' . $section->name . '_P' . $period->number . '.pdf';
        return $pdf->stream($filename);
    }
}
