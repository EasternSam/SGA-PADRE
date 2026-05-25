<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\EvaluationPeriod;
use App\Models\GradeLockPeriod;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use Livewire\Component;

class TeacherDashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $activeYear = AcademicYear::where('status', 'active')->first();

        // Get teacher's section subjects
        $teacherSubjects = SectionSubject::where('teacher_id', $user->id)
            ->when($activeYear, fn($q) => $q->whereHas('section', fn($sq) => $sq->where('academic_year_id', $activeYear->id)))
            ->with(['section.gradeLevel', 'subject'])
            ->get();

        $sectionIds = $teacherSubjects->pluck('section_id')->unique();

        // Sections with student counts
        $sections = Section::whereIn('id', $sectionIds)
            ->with('gradeLevel')
            ->withCount(['students' => fn($q) => $q->where('status', 'Activo')])
            ->get();

        $totalStudents = $sections->sum('students_count');
        $totalSubjects = $teacherSubjects->count();

        // Today's attendance
        $today = now()->toDateString();
        $allStudentIds = Student::whereIn('section_id', $sectionIds)->where('status', 'Activo')->pluck('id');
        $todayPresent = StudentAttendance::whereIn('student_id', $allStudentIds)->whereDate('date', $today)->where('status', 'present')->count();
        $todayAbsent = StudentAttendance::whereIn('student_id', $allStudentIds)->whereDate('date', $today)->where('status', 'absent')->count();

        // Pending grades (sections without grades for current period)
        $currentPeriod = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)
                ->where('end_date', '>=', now()->subDays(30))
                ->orderByDesc('number')
                ->first()
            : null;

        $pendingGrades = [];
        if ($currentPeriod) {
            foreach ($teacherSubjects as $ts) {
                $studentsInSection = Student::where('section_id', $ts->section_id)->where('status', 'Activo')->count();
                $gradesEntered = StudentGrade::where('section_subject_id', $ts->id)
                    ->where('evaluation_period_id', $currentPeriod->id)
                    ->whereNotNull('score')
                    ->count();

                if ($gradesEntered < $studentsInSection) {
                    $isLocked = GradeLockPeriod::isLocked($currentPeriod->id);
                    $pendingGrades[] = [
                        'section'  => ($ts->section?->gradeLevel?->short_name ?? '') . ' ' . ($ts->section?->name ?? ''),
                        'subject'  => $ts->subject?->name ?? '—',
                        'entered'  => $gradesEntered,
                        'total'    => $studentsInSection,
                        'locked'   => $isLocked,
                    ];
                }
            }
        }

        // Recent grades entered by this teacher
        $recentGrades = StudentGrade::whereIn('section_subject_id', $teacherSubjects->pluck('id'))
            ->with(['student', 'sectionSubject.subject'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('livewire.admin.school.teacher-dashboard', [
            'sections'       => $sections,
            'teacherSubjects' => $teacherSubjects,
            'totalStudents'  => $totalStudents,
            'totalSubjects'  => $totalSubjects,
            'todayPresent'   => $todayPresent,
            'todayAbsent'    => $todayAbsent,
            'currentPeriod'  => $currentPeriod,
            'pendingGrades'  => $pendingGrades,
            'recentGrades'   => $recentGrades,
        ])->layout('layouts.dashboard');
    }
}
