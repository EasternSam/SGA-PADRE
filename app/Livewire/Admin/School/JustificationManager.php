<?php

namespace App\Livewire\Admin\School;

use App\Models\AbsenceJustification;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class JustificationManager extends Component
{
    use WithPagination, WithFileUploads;

    public $filterStatus = '';

    // Modal
    public $showModal = false;
    public $studentSearch = '';
    public $student_id = '';
    public $date_from = '';
    public $date_to = '';
    public $reason = 'medical';
    public $description = '';
    public $document;

    public function create()
    {
        $this->reset(['student_id', 'date_from', 'date_to', 'reason', 'description', 'document', 'studentSearch']);
        $this->reason = 'medical';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'student_id'  => 'required|exists:students,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'reason'      => 'required',
        ]);

        $docPath = null;
        if ($this->document) {
            $docPath = $this->document->store('justifications', 'public');
        }

        AbsenceJustification::create([
            'student_id'    => $this->student_id,
            'date_from'     => $this->date_from,
            'date_to'       => $this->date_to,
            'reason'        => $this->reason,
            'description'   => $this->description,
            'document_path' => $docPath,
            'status'        => 'pending',
            'submitted_by'  => auth()->id(),
        ]);

        $this->showModal = false;
        session()->flash('message', 'Justificación registrada.');
    }

    public function approve($id)
    {
        AbsenceJustification::findOrFail($id)->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
        ]);
        session()->flash('message', 'Justificación aprobada.');
    }

    public function reject($id)
    {
        AbsenceJustification::findOrFail($id)->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
        ]);
    }

    public function delete($id)
    {
        AbsenceJustification::findOrFail($id)->delete();
    }

    public function render()
    {
        $justifications = AbsenceJustification::query()
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->with('student')
            ->orderByDesc('created_at')
            ->paginate(20);

        $studentResults = $this->studentSearch && strlen($this->studentSearch) >= 2
            ? Student::where('status', 'Activo')
                ->where(fn($q) =>
                    $q->where('first_name', 'like', "%{$this->studentSearch}%")
                      ->orWhere('last_name', 'like', "%{$this->studentSearch}%")
                )
                ->limit(10)->get()
            : collect();

        return view('livewire.admin.school.justification-manager', [
            'justifications' => $justifications,
            'reasons'        => AbsenceJustification::REASONS,
            'statuses'       => AbsenceJustification::STATUSES,
            'studentResults' => $studentResults,
        ])->layout('layouts.dashboard');
    }
}
