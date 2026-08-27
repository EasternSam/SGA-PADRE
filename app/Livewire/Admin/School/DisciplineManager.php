<?php

namespace App\Livewire\Admin\School;

use App\Models\DisciplineRecord;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class DisciplineManager extends Component
{
    use WithPagination;

    // Filtros
    public $filterSection = '';
    public $filterSeverity = '';
    public $filterCategory = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // Modal
    public $showModal = false;
    public $editingId = null;
    public $student_id = '';
    public $date = '';
    public $severity = 'leve';
    public $category = '';
    public $description = '';
    public $action_taken = '';
    public $parent_notified = false;
    public $follow_up = '';

    // Búsqueda estudiante
    public $studentSearch = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function create()
    {
        $this->reset(['student_id', 'severity', 'category', 'description', 'action_taken', 'parent_notified', 'follow_up', 'editingId', 'studentSearch']);
        $this->date = now()->format('Y-m-d');
        $this->severity = 'leve';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $record = DisciplineRecord::with('student')->findOrFail($id);
        $this->editingId = $record->id;
        $this->student_id = $record->student_id;
        $this->studentSearch = $record->student?->full_name ?? '';
        $this->date = $record->date->format('Y-m-d');
        $this->severity = $record->severity;
        $this->category = $record->category;
        $this->description = $record->description;
        $this->action_taken = $record->action_taken ?? '';
        $this->parent_notified = $record->parent_notified;
        $this->follow_up = $record->follow_up ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'student_id'  => 'required|exists:students,id',
            'date'        => 'required|date',
            'severity'    => 'required|in:leve,grave,muy_grave',
            'category'    => 'required|string',
            'description' => 'required|string|min:10',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();
        if (!$activeYear) {
            session()->flash('error', 'No hay año escolar activo.');
            return;
        }

        DisciplineRecord::updateOrCreate(
            ['id' => $this->editingId],
            [
                'student_id'       => $this->student_id,
                'academic_year_id' => $activeYear->id,
                'date'             => $this->date,
                'severity'         => $this->severity,
                'category'         => $this->category,
                'description'      => $this->description,
                'action_taken'     => $this->action_taken ?: null,
                'reported_by'      => auth()->id(),
                'parent_notified'  => $this->parent_notified,
                'parent_notified_at' => $this->parent_notified ? now() : null,
                'follow_up'        => $this->follow_up ?: null,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editingId ? 'Registro actualizado.' : 'Incidencia registrada exitosamente.');
    }

    public function delete($id)
    {
        DisciplineRecord::findOrFail($id)->delete();
        session()->flash('message', 'Registro eliminado.');
    }

    public function render()
    {
        $activeYear = AcademicYear::where('status', 'active')->first();

        $query = DisciplineRecord::with(['student', 'reporter'])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->when($this->filterSeverity, fn($q) => $q->where('severity', $this->filterSeverity))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('date', '<=', $this->filterDateTo))
            ->when($this->filterSection, function($q) {
                $studentIds = Student::where('section_id', $this->filterSection)->pluck('id');
                return $q->whereIn('student_id', $studentIds);
            })
            ->orderByDesc('date');

        $sections = $activeYear
            ? Section::where('academic_year_id', $activeYear->id)->with('gradeLevel')->orderBy('grade_level_id')->orderBy('name')->get()
            : collect();

        // Estudiantes para búsqueda
        $searchStudents = $this->studentSearch && strlen($this->studentSearch) >= 2
            ? Student::where('status', 'Activo')
                ->where(function($q) {
                    $q->where('first_name', 'like', "%{$this->studentSearch}%")
                      ->orWhere('last_name', 'like', "%{$this->studentSearch}%");
                })
                ->limit(10)
                ->get()
            : collect();

        // Estadísticas
        $stats = [
            'total'     => DisciplineRecord::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->count(),
            'leve'      => DisciplineRecord::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('severity', 'leve')->count(),
            'grave'     => DisciplineRecord::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('severity', 'grave')->count(),
            'muy_grave' => DisciplineRecord::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->where('severity', 'muy_grave')->count(),
            'this_month'=> DisciplineRecord::when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))->whereMonth('date', now()->month)->count(),
        ];

        return view('livewire.admin.school.discipline-manager', [
            'records'        => $query->paginate(20),
            'sections'       => $sections,
            'categories'     => DisciplineRecord::CATEGORIES,
            'severities'     => DisciplineRecord::SEVERITIES,
            'searchStudents' => $searchStudents,
            'stats'          => $stats,
        ])->layout('layouts.dashboard');
    }
}
