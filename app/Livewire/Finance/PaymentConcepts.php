<?php

namespace App\Livewire\Finance;

use App\Models\PaymentConcept;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('app')]
class PaymentConcepts extends Component
{
    use WithPagination;

    public $search = '';
    public $conceptId = null;
    public $name, $description, $is_fixed_amount = false, $default_amount = 0;
    
    public $confirmingDeletion = false;
    public $conceptToDeleteId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_fixed_amount' => 'required|boolean',
        'default_amount' => 'nullable|numeric|min:0'
    ];

    public function render()
    {
        // Se añade la consulta y se pasa la variable $paymentConcepts a la vista
        $paymentConcepts = PaymentConcept::where('name', 'like', '%' . $this->search . '%')
            // ->orWhere('description', 'like', '%' . $this->search . '%') // Comentado hasta ejecutar migración
            ->paginate(10);
            
        return view('livewire.finance.payment-concepts', [
            'paymentConcepts' => $paymentConcepts
        ]);
    }

    public function create()
    {
        $this->resetInput();
        $this->openModal();
    }

    public function openModal()
    {
        $this->dispatch('open-modal', 'concept-modal'); // <-- ¡CORREGIDO!
    }

    public function closeModal()
    {
        $this->dispatch('close'); // <-- ¡CORREGIDO!
    }

    private function resetInput()
    {
        $this->conceptId = null;
        $this->name = '';
        $this->description = '';
        $this->is_fixed_amount = false;
        $this->default_amount = 0;
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        PaymentConcept::updateOrCreate(
            ['id' => $this->conceptId],
            [
                'name' => $this->name,
                'description' => $this->description,
                'is_fixed_amount' => $this->is_fixed_amount,
                'default_amount' => $this->is_fixed_amount ? $this->default_amount : null,
            ]
        );

        session()->flash('message', $this->conceptId ? 'Concepto actualizado.' : 'Concepto creado.');

        $this->closeModal();
        $this->resetInput();
    }

    public function edit($id)
    {
        $concept = PaymentConcept::findOrFail($id);
        $this->conceptId = $id;
        $this->name = $concept->name;
        $this->description = $concept->description;
        $this->is_fixed_amount = (bool) $concept->is_fixed_amount;
        $this->default_amount = $concept->default_amount;

        $this->openModal();
    }

    public function confirmDeletion($id)
    {
        $this->conceptToDeleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        try {
            PaymentConcept::find($this->conceptToDeleteId)->delete();
            session()->flash('message', 'Concepto eliminado.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo eliminar el concepto, es posible que esté en uso.');
        }
        $this->confirmingDeletion = false;
    }
}