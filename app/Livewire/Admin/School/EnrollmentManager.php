<?php

namespace App\Livewire\Admin\School;

use App\Models\SchoolEnrollment;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class EnrollmentManager extends Component
{
    use WithPagination;

    // Filtros
    public $filterStatus = '';
    public $filterGrade = '';
    public $filterType = '';
    public $search = '';

    // Modal inscripción
    public $showModal = false;
    public $editingId = null;
    public $student_id = '';
    public $grade_level_id = '';
    public $section_id = '';
    public $enrollment_type = 'new';
    public $previous_school = '';
    public $notes = '';
    public $studentSearch = '';

    // Modal documentos
    public $showDocsModal = false;
    public $docsEnrollmentId = null;
    public $docs = [];

    public function create()
    {
        $this->reset(['student_id', 'grade_level_id', 'section_id', 'enrollment_type', 'previous_school', 'notes', 'editingId', 'studentSearch']);
        $this->enrollment_type = 'new';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'student_id'     => 'required|exists:students,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'enrollment_type'=> 'required|in:new,renewal,transfer',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            session()->flash('error', 'No hay año escolar activo.');
            return;
        }

        // Verificar si ya está inscrito
        $existing = SchoolEnrollment::where('student_id', $this->student_id)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        if ($existing && !$this->editingId) {
            session()->flash('error', 'Este estudiante ya tiene una inscripción para este año.');
            return;
        }

        $enrollment = SchoolEnrollment::updateOrCreate(
            ['id' => $this->editingId],
            [
                'student_id'      => $this->student_id,
                'academic_year_id'=> $activeYear->id,
                'grade_level_id'  => $this->grade_level_id,
                'section_id'      => $this->section_id ?: null,
                'enrollment_type' => $this->enrollment_type,
                'enrollment_code' => $this->editingId ? null : SchoolEnrollment::generateCode($activeYear->id, $this->grade_level_id),
                'enrollment_date' => now(),
                'previous_school' => $this->previous_school ?: null,
                'notes'           => $this->notes ?: null,
                'processed_by'    => auth()->id(),
                'status'          => 'pending',
            ]
        );

        // Actualizar student con el grado y sección
        $student = Student::find($this->student_id);
        if ($student) {
            $student->update([
                'grade_level_id' => $this->grade_level_id,
                'section_id'     => $this->section_id ?: null,
            ]);
        }

        $this->showModal = false;
        session()->flash('message', 'Inscripción registrada. Código: ' . ($enrollment->enrollment_code ?? 'N/A'));
    }

    public function approve($id)
    {
        $enrollment = SchoolEnrollment::findOrFail($id);
        $enrollment->update(['status' => 'approved']);
        session()->flash('message', 'Inscripción aprobada.');
    }

    public function enroll($id)
    {
        $enrollment = SchoolEnrollment::findOrFail($id);
        $enrollment->update(['status' => 'enrolled']);
        session()->flash('message', 'Estudiante matriculado oficialmente.');
    }

    public function manageDocs($id)
    {
        $enrollment = SchoolEnrollment::findOrFail($id);
        $this->docsEnrollmentId = $id;
        $this->docs = [];
        foreach (SchoolEnrollment::REQUIRED_DOCS as $field => $label) {
            $this->docs[$field] = (bool)$enrollment->{$field};
        }
        $this->showDocsModal = true;
    }

    public function saveDocs()
    {
        $enrollment = SchoolEnrollment::findOrFail($this->docsEnrollmentId);
        $enrollment->update($this->docs);
        $this->showDocsModal = false;
        session()->flash('message', 'Documentos actualizados.');
    }

    public function delete($id)
    {
        SchoolEnrollment::findOrFail($id)->delete();
        session()->flash('message', 'Inscripción eliminada.');
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $query = SchoolEnrollment::with(['student', 'gradeLevel', 'section'])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterGrade, fn($q) => $q->where('grade_level_id', $this->filterGrade))
            ->when($this->filterType, fn($q) => $q->where('enrollment_type', $this->filterType))
            ->when($this->search, function($q) {
                $q->whereHas('student', function($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('created_at');

        // Stats
        $stats = [
            'total'    => $query->clone()->count(),
            'pending'  => SchoolEnrollment::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('status', 'pending')->count(),
            'approved' => SchoolEnrollment::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('status', 'approved')->count(),
            'enrolled' => SchoolEnrollment::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('status', 'enrolled')->count(),
        ];

        $gradeLevels = GradeLevel::active()->ordered()->get();
        $sections = $this->grade_level_id && $activeYear
            ? Section::where('academic_year_id', $activeYear->id)->where('grade_level_id', $this->grade_level_id)->orderBy('name')->get()
            : collect();

        $searchStudents = $this->studentSearch && strlen($this->studentSearch) >= 2
            ? Student::where('first_name', 'like', "%{$this->studentSearch}%")
                ->orWhere('last_name', 'like', "%{$this->studentSearch}%")
                ->limit(10)->get()
            : collect();

        return view('livewire.admin.school.enrollment-manager', [
            'enrollments'    => $query->paginate(25),
            'activeYear'     => $activeYear,
            'gradeLevels'    => $gradeLevels,
            'sections'       => $sections,
            'searchStudents' => $searchStudents,
            'stats'          => $stats,
            'statuses'       => SchoolEnrollment::STATUSES,
            'types'          => SchoolEnrollment::ENROLLMENT_TYPES,
            'requiredDocs'   => SchoolEnrollment::REQUIRED_DOCS,
        ])->layout('layouts.dashboard');
    }
}
