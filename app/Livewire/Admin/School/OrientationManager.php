<?php

namespace App\Livewire\Admin\School;

use App\Models\AcademicYear;
use App\Models\OrientationRecord;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class OrientationManager extends Component
{
    use WithPagination;

    public $filterStatus = '';
    public $filterPriority = '';
    public $filterType = '';
    public $search = '';

    // Modal
    public $showModal = false;
    public $editId = null;
    public $studentSearch = '';
    public $student_id = '';
    public $type = 'interview';
    public $title = '';
    public $description = '';
    public $findings = '';
    public $recommendations = '';
    public $priority = 'medium';
    public $status = 'open';
    public $next_followup = '';
    public $is_confidential = false;

    public function create()
    {
        $this->reset(['editId', 'student_id', 'type', 'title', 'description', 'findings', 'recommendations', 'priority', 'status', 'next_followup', 'is_confidential', 'studentSearch']);
        $this->type = 'interview';
        $this->priority = 'medium';
        $this->status = 'open';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $r = OrientationRecord::findOrFail($id);
        $this->editId = $r->id;
        $this->student_id = $r->student_id;
        $this->type = $r->type;
        $this->title = $r->title;
        $this->description = $r->description ?? '';
        $this->findings = $r->findings ?? '';
        $this->recommendations = $r->recommendations ?? '';
        $this->priority = $r->priority;
        $this->status = $r->status;
        $this->next_followup = $r->next_followup?->format('Y-m-d') ?? '';
        $this->is_confidential = $r->is_confidential;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'student_id' => 'required|exists:students,id',
            'title'      => 'required|string|max:255',
            'type'       => 'required',
        ]);

        $activeYear = AcademicYear::where('status', 'active')->first();

        OrientationRecord::updateOrCreate(
            ['id' => $this->editId],
            [
                'student_id'       => $this->student_id,
                'academic_year_id' => $activeYear?->id,
                'type'             => $this->type,
                'title'            => $this->title,
                'description'      => $this->description ?: null,
                'findings'         => $this->findings ?: null,
                'recommendations'  => $this->recommendations ?: null,
                'priority'         => $this->priority,
                'status'           => $this->status,
                'next_followup'    => $this->next_followup ?: null,
                'counselor_id'     => auth()->id(),
                'is_confidential'  => $this->is_confidential,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editId ? 'Registro actualizado.' : 'Registro creado.');
    }

    public function resolve($id)
    {
        OrientationRecord::findOrFail($id)->update(['status' => 'resolved']);
        session()->flash('message', 'Caso resuelto.');
    }

    public function delete($id)
    {
        OrientationRecord::findOrFail($id)->delete();
    }

    public function render()
    {
        $records = OrientationRecord::query()
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->search, fn($q) =>
                $q->where(fn($sq) =>
                    $sq->where('title', 'like', "%{$this->search}%")
                       ->orWhereHas('student', fn($stq) =>
                            $stq->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                       )
                )
            )
            ->with(['student', 'counselor'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Stats
        $openCases = OrientationRecord::whereIn('status', ['open', 'in_progress'])->count();
        $urgentCases = OrientationRecord::where('priority', 'urgent')->whereIn('status', ['open', 'in_progress'])->count();
        $followupDue = OrientationRecord::where('next_followup', '<=', now())->whereIn('status', ['open', 'in_progress'])->count();

        $studentResults = $this->studentSearch && strlen($this->studentSearch) >= 2
            ? Student::where('status', 'Activo')
                ->where(fn($q) =>
                    $q->where('first_name', 'like', "%{$this->studentSearch}%")
                      ->orWhere('last_name', 'like', "%{$this->studentSearch}%")
                )->limit(10)->get()
            : collect();

        return view('livewire.admin.school.orientation-manager', [
            'records'        => $records,
            'types'          => OrientationRecord::TYPES,
            'priorities'     => OrientationRecord::PRIORITIES,
            'statuses'       => OrientationRecord::STATUSES,
            'openCases'      => $openCases,
            'urgentCases'    => $urgentCases,
            'followupDue'    => $followupDue,
            'studentResults' => $studentResults,
        ])->layout('layouts.dashboard');
    }
}
