<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\DisciplineRecord;
use App\Models\GradeLevel;
use App\Models\SchoolAnnouncement;
use App\Models\SchoolConfig;
use App\Models\SchoolEnrollment;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use App\Models\Subject;
use Livewire\Component;

class SchoolDashboard extends Component
{
    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();
        $schoolConfig = SchoolConfig::current();

        // Enrollment stats
        $enrollment = [
            'total_students'  => Student::where('status', 'Activo')->count(),
            'total_sections'  => $activeYear ? Section::where('academic_year_id', $activeYear->id)->count() : 0,
            'total_subjects'  => Subject::active()->count(),
            'grade_levels'    => GradeLevel::active()->count(),
        ];

        // Students per grade level
        $studentsByGrade = [];
        if ($activeYear) {
            $grades = GradeLevel::active()->ordered()->get();
            foreach ($grades as $grade) {
                $count = Student::where('status', 'Activo')
                    ->where('grade_level_id', $grade->id)
                    ->count();
                if ($count > 0) {
                    $studentsByGrade[] = [
                        'name'  => $grade->short_name ?? $grade->name,
                        'count' => $count,
                    ];
                }
            }
        }

        // Attendance today
        $todayAttendance = [
            'present' => StudentAttendance::whereDate('date', today())->where('status', 'present')->count(),
            'absent'  => StudentAttendance::whereDate('date', today())->where('status', 'absent')->count(),
            'late'    => StudentAttendance::whereDate('date', today())->where('status', 'late')->count(),
            'total'   => StudentAttendance::whereDate('date', today())->count(),
        ];

        // Discipline this month
        $disciplineMonth = [
            'total'     => DisciplineRecord::whereMonth('date', now()->month)->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->count(),
            'leve'      => DisciplineRecord::whereMonth('date', now()->month)->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('severity', 'leve')->count(),
            'grave'     => DisciplineRecord::whereMonth('date', now()->month)->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('severity', 'grave')->count(),
            'muy_grave' => DisciplineRecord::whereMonth('date', now()->month)->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('severity', 'muy_grave')->count(),
        ];

        // Enrollment status
        $enrollmentStatus = [];
        if ($activeYear) {
            $enrollmentStatus = [
                'pending'  => SchoolEnrollment::where('academic_year_id', $activeYear->id)->where('status', 'pending')->count(),
                'approved' => SchoolEnrollment::where('academic_year_id', $activeYear->id)->where('status', 'approved')->count(),
                'enrolled' => SchoolEnrollment::where('academic_year_id', $activeYear->id)->where('status', 'enrolled')->count(),
            ];
        }

        // Recent announcements
        $recentAnnouncements = SchoolAnnouncement::published()
            ->orderByDesc('publish_date')
            ->limit(5)
            ->get();

        // Academic performance (average per grade level)
        $gradePerformance = [];
        if ($activeYear) {
            $periods = $activeYear->evaluationPeriods()->orderBy('number')->get();
            $latestPeriod = $periods->last();
            if ($latestPeriod) {
                foreach (GradeLevel::active()->ordered()->get() as $grade) {
                    $studentIds = Student::where('grade_level_id', $grade->id)->where('status', 'Activo')->pluck('id');
                    $avg = StudentGrade::whereIn('student_id', $studentIds)
                        ->where('evaluation_period_id', $latestPeriod->id)
                        ->avg('score');
                    if ($avg) {
                        $gradePerformance[] = [
                            'name' => $grade->short_name ?? $grade->name,
                            'avg'  => round($avg, 1),
                        ];
                    }
                }
            }
        }

        return view('livewire.admin.school.school-dashboard', compact(
            'activeYear', 'schoolConfig', 'enrollment', 'studentsByGrade',
            'todayAttendance', 'disciplineMonth', 'enrollmentStatus',
            'recentAnnouncements', 'gradePerformance'
        ))->layout('layouts.dashboard');
    }
}
