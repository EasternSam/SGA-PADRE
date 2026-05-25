<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLevel;
use App\Models\SchoolConfig;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MinerdReportsPdfController extends Controller
{
    /**
     * RE-1: Registro de Estudiantes MINERD
     */
    public function re1()
    {
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();

        $sections = Section::where('academic_year_id', $activeYear?->id)
            ->with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $data = [];
        foreach ($sections as $section) {
            $students = Student::where('section_id', $section->id)
                ->where('status', 'Activo')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $males = $students->filter(fn($s) => in_array(strtolower($s->gender ?? ''), ['m', 'masculino', 'male']))->count();
            $females = $students->filter(fn($s) => in_array(strtolower($s->gender ?? ''), ['f', 'femenino', 'female']))->count();

            $data[] = [
                'section'  => $section,
                'students' => $students,
                'males'    => $males,
                'females'  => $females,
                'total'    => $students->count(),
            ];
        }

        $totalStudents = collect($data)->sum('total');
        $totalMales = collect($data)->sum('males');
        $totalFemales = collect($data)->sum('females');

        $viewData = compact('schoolConfig', 'activeYear', 'data', 'totalStudents', 'totalMales', 'totalFemales');

        $pdf = Pdf::loadView('reports.minerd.re1', $viewData);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('RE1_' . ($activeYear?->name ?? '') . '.pdf');
    }

    /**
     * RE-2: Calificaciones por Período MINERD
     */
    public function re2(EvaluationPeriod $period)
    {
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();

        $sections = Section::where('academic_year_id', $activeYear?->id)
            ->with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $sectionData = [];
        foreach ($sections as $section) {
            $students = Student::where('section_id', $section->id)
                ->where('status', 'Activo')
                ->orderBy('last_name')->orderBy('first_name')
                ->get();

            $sectionSubjects = \App\Models\SectionSubject::where('section_id', $section->id)
                ->with('subject')->get();

            $matrix = [];
            $approved = 0;
            $failed = 0;

            foreach ($students as $student) {
                $grades = StudentGrade::where('student_id', $student->id)
                    ->where('evaluation_period_id', $period->id)
                    ->get()->keyBy('section_subject_id');

                $scores = [];
                $total = 0;
                $count = 0;

                foreach ($sectionSubjects as $ss) {
                    $g = $grades->get($ss->id);
                    $score = $g?->score;
                    $scores[$ss->id] = $score;
                    if ($score !== null) { $total += $score; $count++; }
                }

                $avg = $count > 0 ? round($total / $count, 1) : null;
                if ($avg !== null) { $avg >= 70 ? $approved++ : $failed++; }

                $matrix[] = [
                    'student' => $student,
                    'scores'  => $scores,
                    'avg'     => $avg,
                ];
            }

            $sectionData[] = [
                'section'  => $section,
                'subjects' => $sectionSubjects,
                'matrix'   => $matrix,
                'approved' => $approved,
                'failed'   => $failed,
            ];
        }

        $viewData = compact('schoolConfig', 'activeYear', 'period', 'sectionData');

        $pdf = Pdf::loadView('reports.minerd.re2', $viewData);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('RE2_' . $period->name . '.pdf');
    }

    /**
     * RE-3: Reporte de Asistencia MINERD
     */
    public function re3($month = null)
    {
        $schoolConfig = SchoolConfig::current();
        $activeYear = AcademicYear::where('status', 'active')->first();
        $month = $month ?? now()->month;
        $year = now()->year;

        $sections = Section::where('academic_year_id', $activeYear?->id)
            ->with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $monthName = $startDate->translatedFormat('F Y');

        $sectionData = [];
        foreach ($sections as $section) {
            $students = Student::where('section_id', $section->id)
                ->where('status', 'Activo')
                ->orderBy('last_name')->orderBy('first_name')
                ->get();

            $sectionStats = [
                'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0,
            ];

            $studentRows = [];
            foreach ($students as $student) {
                $attendance = StudentAttendance::where('student_id', $student->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                $stats = [
                    'present' => $attendance->where('status', 'present')->count(),
                    'absent'  => $attendance->where('status', 'absent')->count(),
                    'late'    => $attendance->where('status', 'late')->count(),
                    'excused' => $attendance->where('status', 'excused')->count(),
                    'total'   => $attendance->count(),
                ];

                $sectionStats['present'] += $stats['present'];
                $sectionStats['absent'] += $stats['absent'];
                $sectionStats['late'] += $stats['late'];
                $sectionStats['excused'] += $stats['excused'];
                $sectionStats['total'] += $stats['total'];

                $pct = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 1) : null;

                $studentRows[] = [
                    'student' => $student,
                    'stats'   => $stats,
                    'pct'     => $pct,
                ];
            }

            $sectionData[] = [
                'section'  => $section,
                'students' => $studentRows,
                'stats'    => $sectionStats,
            ];
        }

        $viewData = compact('schoolConfig', 'activeYear', 'monthName', 'month', 'sectionData');

        $pdf = Pdf::loadView('reports.minerd.re3', $viewData);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('RE3_' . $monthName . '.pdf');
    }
}
