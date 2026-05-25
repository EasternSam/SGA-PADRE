<?php

namespace App\Livewire\Admin\School;

use App\Models\Guardian;
use App\Models\ParentAccessToken;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class ParentTokenManager extends Component
{
    use WithPagination;

    public $search = '';

    public $showGenerateModal = false;
    public $studentSearch = '';
    public $student_id = '';
    public $guardian_id = '';

    // Generated result
    public $generatedToken = null;
    public $generatedPin = null;

    public function generate()
    {
        $this->reset(['student_id', 'guardian_id', 'studentSearch', 'generatedToken', 'generatedPin']);
        $this->showGenerateModal = true;
    }

    public function createToken()
    {
        $this->validate([
            'student_id'  => 'required|exists:students,id',
            'guardian_id' => 'required|exists:guardians,id',
        ]);

        $token = ParentAccessToken::generateForStudent($this->guardian_id, $this->student_id);

        $this->generatedToken = $token->token;
        $this->generatedPin = $token->pin;

        session()->flash('message', 'Token generado exitosamente.');
    }

    public function revoke($id)
    {
        ParentAccessToken::findOrFail($id)->update(['is_active' => false]);
        session()->flash('message', 'Token revocado.');
    }

    public function render()
    {
        $tokens = ParentAccessToken::query()
            ->when($this->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                )
            )
            ->with(['guardian', 'student'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $studentResults = $this->studentSearch && strlen($this->studentSearch) >= 2
            ? Student::where('status', 'Activo')
                ->where(fn($q) =>
                    $q->where('first_name', 'like', "%{$this->studentSearch}%")
                      ->orWhere('last_name', 'like', "%{$this->studentSearch}%")
                )->limit(10)->get()
            : collect();

        $guardians = $this->student_id
            ? Guardian::whereHas('students', fn($q) => $q->where('student_id', $this->student_id))->get()
            : collect();

        $activeTokens = ParentAccessToken::where('is_active', true)->count();
        $totalTokens = ParentAccessToken::count();

        return view('livewire.admin.school.parent-token-manager', [
            'tokens'         => $tokens,
            'studentResults' => $studentResults,
            'guardians'      => $guardians,
            'activeTokens'   => $activeTokens,
            'totalTokens'    => $totalTokens,
        ])->layout('layouts.dashboard');
    }
}
