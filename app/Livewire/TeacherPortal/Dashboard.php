<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TeacherAssignment;
use App\Models\SchoolEnrollment;
use App\Models\SectionSubject;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public $teacher;
    public $assignments = [];
    public $totalClasses = 0;
    public $totalStudents = 0;
    public $hasHomeroom = false;
    public $homeroomSectionName = '';
    public $chartLabels = [];
    public $chartData = [];

    public function mount()
    {
        $this->teacher = Auth::user();
        $teacherId = $this->teacher->id;

        // 1. Get the active academic year
        $activeYear = AcademicYear::where('status', 'active')->first() 
            ?? AcademicYear::orderByDesc('id')->first();

        if ($activeYear) {
            // 2. Load teacher assignments for the active year
            $this->assignments = TeacherAssignment::where('teacher_id', $teacherId)
                ->where('academic_year_id', $activeYear->id)
                ->with(['section.gradeLevel', 'subject'])
                ->get();
        }

        $this->totalClasses = count($this->assignments);

        // 3. Calculate total unique students taught across all assigned sections
        $sectionIds = collect($this->assignments)->pluck('section_id')->unique()->toArray();
        
        if (!empty($sectionIds)) {
            $this->totalStudents = SchoolEnrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'enrolled')
                ->where('academic_year_id', $activeYear->id)
                ->count();
        }

        // 4. Check if they are a homeroom/tutor teacher
        $homeroomAssignment = collect($this->assignments)->firstWhere('is_homeroom', true);
        if ($homeroomAssignment && $homeroomAssignment->section) {
            $this->hasHomeroom = true;
            $this->homeroomSectionName = $homeroomAssignment->section->full_name;
        }

        // 5. Prepare chart data and attach IDs
        $labels = [];
        $data = [];
        foreach ($this->assignments as $assignment) {
            if ($assignment->section && $assignment->subject) {
                // Find and attach SectionSubject ID for route mapping
                $sectionSubject = SectionSubject::where('section_id', $assignment->section_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->first();
                
                $assignment->section_subject_id = $sectionSubject?->id;

                $sectionName = $assignment->section->full_name;
                $subjectName = $assignment->subject->name;
                $labels[] = Str::limit($sectionName . ' - ' . $subjectName, 30);
                
                // Get student count for this section
                $studentCount = SchoolEnrollment::where('section_id', $assignment->section_id)
                    ->where('status', 'enrolled')
                    ->where('academic_year_id', $activeYear->id)
                    ->count();
                
                $assignment->enrollments_count = $studentCount;
                $data[] = $studentCount;
            }
        }
        $this->chartLabels = $labels;
        $this->chartData = $data;
    }

    public function render()
    {
        return view('livewire.teacher-portal.dashboard');
    }
}