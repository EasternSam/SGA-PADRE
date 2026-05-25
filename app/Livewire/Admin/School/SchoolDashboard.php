<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\DisciplineRecord;
use App\Models\EvaluationPeriod;
use App\Models\SchoolAlert;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use Carbon\Carbon;
use Livewire\Component;

class SchoolDashboard extends Component
{
    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        // General stats
        $totalStudents = Student::where('status', 'Activo')->count();
        $totalSections = $activeYear ? Section::where('academic_year_id', $activeYear->id)->count() : 0;
        $totalTeachers = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['Docente', 'Teacher', 'Profesor']))->count();

        // Gender breakdown
        $males = Student::where('status', 'Activo')
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(gender)'), ['m', 'masculino', 'male'])->count();
        $females = $totalStudents - $males;

        // Attendance today
        $today = now()->toDateString();
        $todayPresent = StudentAttendance::whereDate('date', $today)->where('status', 'present')->count();
        $todayAbsent = StudentAttendance::whereDate('date', $today)->where('status', 'absent')->count();
        $todayLate = StudentAttendance::whereDate('date', $today)->where('status', 'late')->count();
        $todayTotal = $todayPresent + $todayAbsent + $todayLate;

        // This week attendance
        $weekStart = now()->startOfWeek();
        $weekPresent = StudentAttendance::where('date', '>=', $weekStart)->where('status', 'present')->count();
        $weekTotal = StudentAttendance::where('date', '>=', $weekStart)->count();
        $weekPct = $weekTotal > 0 ? round(($weekPresent / $weekTotal) * 100, 1) : 0;

        // Academic overview (latest period)
        $latestPeriod = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->whereHas('grades')
                ->orderByDesc('number')->first()
            : null;

        $gradeStats = ['avg' => null, 'above70' => 0, 'below70' => 0, 'total' => 0];
        if ($latestPeriod) {
            $grades = StudentGrade::where('evaluation_period_id', $latestPeriod->id)
                ->whereNotNull('score')->get();
            $gradeStats['avg'] = round($grades->avg('score'), 1);
            $gradeStats['above70'] = $grades->where('score', '>=', 70)->count();
            $gradeStats['below70'] = $grades->where('score', '<', 70)->count();
            $gradeStats['total'] = $grades->count();
        }

        // Active alerts
        $activeAlerts = SchoolAlert::where('is_resolved', false)
            ->orderByDesc('severity')
            ->limit(5)->get();
        $alertCount = SchoolAlert::where('is_resolved', false)->count();
        $criticalAlerts = SchoolAlert::where('is_resolved', false)->where('severity', 'critical')->count();

        // Discipline this month
        $monthDiscipline = DisciplineRecord::whereMonth('date', now()->month)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        // Section ranking by attendance
        $sectionRanking = [];
        if ($activeYear) {
            $sections = Section::where('academic_year_id', $activeYear->id)->with('gradeLevel')->get();
            foreach ($sections as $sec) {
                $studIds = Student::where('section_id', $sec->id)->where('status', 'Activo')->pluck('id');
                $attTotal = StudentAttendance::whereIn('student_id', $studIds)->where('date', '>=', $weekStart)->count();
                $attPresent = StudentAttendance::whereIn('student_id', $studIds)->where('date', '>=', $weekStart)->where('status', 'present')->count();
                $sectionRanking[] = [
                    'name' => ($sec->gradeLevel?->short_name ?? '') . ' ' . $sec->name,
                    'students' => $studIds->count(),
                    'pct' => $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : null,
                ];
            }
            usort($sectionRanking, fn($a, $b) => ($b['pct'] ?? 0) - ($a['pct'] ?? 0));
            $sectionRanking = array_slice($sectionRanking, 0, 8);
        }

        return view('livewire.admin.school.school-dashboard', [
            'activeYear'      => $activeYear,
            'totalStudents'   => $totalStudents,
            'totalSections'   => $totalSections,
            'totalTeachers'   => $totalTeachers,
            'males'           => $males,
            'females'         => $females,
            'todayPresent'    => $todayPresent,
            'todayAbsent'     => $todayAbsent,
            'todayLate'       => $todayLate,
            'todayTotal'      => $todayTotal,
            'weekPct'         => $weekPct,
            'latestPeriod'    => $latestPeriod,
            'gradeStats'      => $gradeStats,
            'activeAlerts'    => $activeAlerts,
            'alertCount'      => $alertCount,
            'criticalAlerts'  => $criticalAlerts,
            'monthDiscipline' => $monthDiscipline,
            'sectionRanking'  => $sectionRanking,
        ])->layout('layouts.dashboard');
    }
}
