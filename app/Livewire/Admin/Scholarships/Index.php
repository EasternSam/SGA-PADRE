<?php

namespace App\Livewire\Admin\Scholarships;

use App\Models\Scholarship;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $isModalOpen = false;
    
    // Properties
    public $scholarshipId;
    public $name;
    public $discount_percentage;
    public $description;
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'discount_percentage' => 'required|numeric|min:0|max:100',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $scholarships = Scholarship::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.scholarships.index', compact('scholarships'))
            ->layout('layouts.dashboard');
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['scholarshipId', 'name', 'discount_percentage', 'description', 'is_active']);
        $this->is_active = true;
        
        // Emulate x-modal dispatch if we use custom alpine modals, or we just set boolean
        // For standard x-modal of Breeze/Livewire we dispatch event.
        $this->dispatch('open-modal', 'scholarship-modal');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $scholarship = Scholarship::findOrFail($id);
        $this->scholarshipId = $scholarship->id;
        $this->name = $scholarship->name;
        $this->discount_percentage = $scholarship->discount_percentage;
        $this->description = $scholarship->description;
        $this->is_active = $scholarship->is_active;
        
        $this->dispatch('open-modal', 'scholarship-modal');
    }

    public function save()
    {
        $this->validate();

        Scholarship::updateOrCreate(
            ['id' => $this->scholarshipId],
            [
                'name' => $this->name,
                'discount_percentage' => $this->discount_percentage,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        session()->flash('message', 'Beca guardada exitosamente.');
        $this->dispatch('close-modal', 'scholarship-modal');
    }

    public function toggleActive($id)
    {
        $scholarship = Scholarship::findOrFail($id);
        $scholarship->update(['is_active' => !$scholarship->is_active]);
        session()->flash('message', 'Estado de la beca actualizado.');
    }

    public function confirmDelete($id)
    {
        $this->scholarshipId = $id;
        $this->dispatch('open-modal', 'confirm-delete-modal');
    }

    public function delete()
    {
        Scholarship::findOrFail($this->scholarshipId)->delete();
        session()->flash('message', 'Beca eliminada exitosamente.');
        $this->dispatch('close-modal', 'confirm-delete-modal');
    }
}
