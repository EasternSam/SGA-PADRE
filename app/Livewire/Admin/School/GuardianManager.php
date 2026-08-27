<?php

namespace App\Livewire\Admin\School;

use App\Models\Guardian;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class GuardianManager extends Component
{
    use WithPagination;

    public $search = '';

    // Modal CRUD
    public $showModal = false;
    public $editingId = null;
    public $first_name = '';
    public $last_name = '';
    public $cedula = '';
    public $phone = '';
    public $phone_alt = '';
    public $email = '';
    public $address = '';
    public $relationship = 'padre';
    public $occupation = '';
    public $workplace = '';
    public $is_emergency_contact = true;

    // Link student modal
    public $showLinkModal = false;
    public $linkGuardianId = null;
    public $linkGuardianName = '';
    public $linkStudentSearch = '';
    public $linkStudentId = '';
    public $linkIsPrimary = false;
    public $linkedStudents = [];

    protected $rules = [
        'first_name'   => 'required|string|max:100',
        'last_name'    => 'required|string|max:100',
        'relationship' => 'required',
        'phone'        => 'nullable|string|max:20',
    ];

    public function create()
    {
        $this->reset(['first_name', 'last_name', 'cedula', 'phone', 'phone_alt', 'email', 'address', 'relationship', 'occupation', 'workplace', 'is_emergency_contact', 'editingId']);
        $this->relationship = 'padre';
        $this->is_emergency_contact = true;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $g = Guardian::findOrFail($id);
        $this->editingId = $g->id;
        $this->first_name = $g->first_name;
        $this->last_name = $g->last_name;
        $this->cedula = $g->cedula;
        $this->phone = $g->phone;
        $this->phone_alt = $g->phone_alt;
        $this->email = $g->email;
        $this->address = $g->address;
        $this->relationship = $g->relationship;
        $this->occupation = $g->occupation;
        $this->workplace = $g->workplace;
        $this->is_emergency_contact = $g->is_emergency_contact;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Guardian::updateOrCreate(
            ['id' => $this->editingId],
            [
                'first_name'           => $this->first_name,
                'last_name'            => $this->last_name,
                'cedula'               => $this->cedula ?: null,
                'phone'                => $this->phone ?: null,
                'phone_alt'            => $this->phone_alt ?: null,
                'email'                => $this->email ?: null,
                'address'              => $this->address ?: null,
                'relationship'         => $this->relationship,
                'occupation'           => $this->occupation ?: null,
                'workplace'            => $this->workplace ?: null,
                'is_emergency_contact' => $this->is_emergency_contact,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editingId ? 'Tutor actualizado.' : 'Tutor registrado exitosamente.');
    }

    public function delete($id)
    {
        Guardian::findOrFail($id)->delete();
        session()->flash('message', 'Tutor eliminado.');
    }

    public function openLink($guardianId)
    {
        $g = Guardian::with('students')->findOrFail($guardianId);
        $this->linkGuardianId = $g->id;
        $this->linkGuardianName = $g->full_name;
        $this->linkedStudents = $g->students->map(fn($s) => [
            'id'         => $s->id,
            'name'       => $s->full_name,
            'is_primary' => $s->pivot->is_primary,
        ])->toArray();
        $this->linkStudentSearch = '';
        $this->linkStudentId = '';
        $this->linkIsPrimary = false;
        $this->showLinkModal = true;
    }

    public function linkStudent()
    {
        if (!$this->linkStudentId || !$this->linkGuardianId) return;

        $guardian = Guardian::find($this->linkGuardianId);
        if ($guardian->students()->where('student_id', $this->linkStudentId)->exists()) {
            session()->flash('link-error', 'Este estudiante ya está vinculado.');
            return;
        }

        $guardian->students()->attach($this->linkStudentId, ['is_primary' => $this->linkIsPrimary]);
        $this->openLink($this->linkGuardianId); // Refresh
        session()->flash('link-message', 'Estudiante vinculado.');
    }

    public function unlinkStudent($studentId)
    {
        Guardian::find($this->linkGuardianId)?->students()->detach($studentId);
        $this->openLink($this->linkGuardianId);
    }

    public function render()
    {
        $guardians = Guardian::query()
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
                   ->orWhere('cedula', 'like', "%{$this->search}%")
                   ->orWhere('phone', 'like', "%{$this->search}%")
            ))
            ->withCount('students')
            ->orderBy('last_name')
            ->paginate(20);

        $studentResults = $this->linkStudentSearch && strlen($this->linkStudentSearch) >= 2
            ? Student::where('status', 'Activo')
                ->where(fn($q) =>
                    $q->where('first_name', 'like', "%{$this->linkStudentSearch}%")
                      ->orWhere('last_name', 'like', "%{$this->linkStudentSearch}%")
                )
                ->limit(10)->get()
            : collect();

        return view('livewire.admin.school.guardian-manager', [
            'guardians'       => $guardians,
            'relationships'   => Guardian::RELATIONSHIPS,
            'studentResults'  => $studentResults,
        ])->layout('layouts.dashboard');
    }
}
