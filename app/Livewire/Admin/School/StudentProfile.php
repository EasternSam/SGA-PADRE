<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\DisciplineRecord;
use App\Models\EvaluationPeriod;
use App\Models\SchoolEnrollment;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use Livewire\Component;

class StudentProfile extends Component
{
    public $student_id = '';
    public $search = '';
    public $studentData = null;

    public function updatedSearch()
    {
        $this->student_id = '';
        $this->studentData = null;
    }

    public function selectStudent($id)
    {
        $this->student_id = $id;
        $this->loadProfile();
    }

    public function loadProfile()
    {
        if (!$this->student_id) return;

        $student = Student::with(['gradeLevel'])->find($this->student_id);
        if (!$student) return;

        $activeYear = AcademicYear::where('status', 'active')->first();
        $section = Section::with('gradeLevel')->find($student->section_id);

        // Enrollment
        $enrollment = SchoolEnrollment::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->first();

        // Grades per period
        $periods = $activeYear
            ? EvaluationPeriod::where('academic_year_id', $activeYear->id)->orderBy('number')->get()
            : collect();

        $gradesByPeriod = [];
        foreach ($periods as $period) {
            $grades = StudentGrade::where('student_id', $student->id)
                ->where('evaluation_period_id', $period->id)
                ->with('sectionSubject.subject')
                ->get();

            $avg = $grades->whereNotNull('score')->avg('score');
            $gradesByPeriod[] = [
                'period' => $period,
                'grades' => $grades,
                'avg'    => $avg ? round($avg, 1) : null,
            ];
        }

        // Attendance summary
        $attendanceSummary = [
            'present' => StudentAttendance::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))->where('status', 'present')->count(),
            'absent'  => StudentAttendance::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))->where('status', 'absent')->count(),
            'late'    => StudentAttendance::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))->where('status', 'late')->count(),
            'total'   => StudentAttendance::where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->whereDate('date', '>=', $activeYear->start_date))->count(),
        ];

        // Discipline
        $disciplineRecords = DisciplineRecord::where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        $this->studentData = [
            'student'    => $student->toArray(),
            'section'    => $section?->toArray(),
            'enrollment' => $enrollment?->toArray(),
            'grades'     => $gradesByPeriod,
            'attendance' => $attendanceSummary,
            'discipline' => $disciplineRecords->toArray(),
            'activeYear' => $activeYear?->toArray(),
        ];
    }

    public function render()
    {
        $searchResults = $this->search && strlen($this->search) >= 2
            ? Student::where('status', 'Activo')
                ->where(function($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('student_id', 'like', "%{$this->search}%");
                })
                ->limit(15)
                ->get()
            : collect();

        return view('livewire.admin.school.student-profile', [
            'searchResults' => $searchResults,
        ])->layout('layouts.dashboard');
    }
}
