<?php

namespace App\Livewire\Finance;

use App\Models\PaymentConcept;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class PaymentConcepts extends Component
{
    use WithPagination;

    public $search = '';
    public $conceptId = null;
    
    // Campos del formulario
    public $name;
    public $description;
    public $is_fixed_amount = false;
    public $amount = 0.00; // Cambiado de default_amount a amount para coincidir con la BD
    
    // Estados de Modales
    public $confirmingDeletion = false;
    public $conceptToDeleteId = null;
    public $confirmingMassDeletion = false;

    protected $paginationTheme = 'tailwind';

    protected function rules() 
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_fixed_amount' => 'boolean',
            'amount' => 'nullable|numeric|min:0',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $paymentConcepts = PaymentConcept::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);
            
        return view('livewire.finance.payment-concepts', [
            'paymentConcepts' => $paymentConcepts
        ]);
    }

    public function create()
    {
        $this->resetInput();
        $this->dispatch('open-modal', 'concept-modal');
    }

    private function resetInput()
    {
        $this->conceptId = null;
        $this->name = '';
        $this->description = '';
        $this->is_fixed_amount = false;
        $this->amount = 0.00;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $concept = PaymentConcept::findOrFail($id);
        
        $this->conceptId = $id;
        $this->name = $concept->name;
        $this->description = $concept->description;
        
        // Determinamos si es fijo si el monto es mayor a 0
        $this->amount = $concept->amount ?? 0.00;
        $this->is_fixed_amount = ($this->amount > 0);

        $this->resetValidation();
        $this->dispatch('open-modal', 'concept-modal');
    }

    public function store()
    {
        $this->validate();

        // Si no es monto fijo, guardamos 0 o null
        $finalAmount = $this->is_fixed_amount ? $this->amount : 0;

        PaymentConcept::updateOrCreate(
            ['id' => $this->conceptId],
            [
                'name' => $this->name,
                'description' => $this->description,
                // Guardamos directamente en 'amount'
                'amount' => $finalAmount,
                // 'is_fixed_amount' no suele ser columna en BD, se deduce si amount > 0
                // pero si tienes la columna, descomenta la siguiente línea:
                // 'is_fixed_amount' => $this->is_fixed_amount, 
            ]
        );

        $action = $this->conceptId ? 'actualizado' : 'creado';
        session()->flash('message', "Concepto $action correctamente.");

        $this->dispatch('close-modal', 'concept-modal');
        $this->resetInput();
    }

    // --- LÓGICA DE ELIMINACIÓN ---

    public function confirmDeletion($id)
    {
        $this->conceptToDeleteId = $id;
        $this->dispatch('open-modal', 'confirm-deletion-modal');
    }

    public function delete()
    {
        try {
            $concept = PaymentConcept::find($this->conceptToDeleteId);
            
            if ($concept) {
                // Verificar si tiene pagos asociados para evitar errores de integridad
                if ($concept->payments()->count() > 0) {
                    session()->flash('error', 'No se puede eliminar: Este concepto ya tiene historial de pagos asociados.');
                } else {
                    $concept->delete();
                    session()->flash('message', 'Concepto eliminado.');
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }

        $this->dispatch('close-modal', 'confirm-deletion-modal');
        $this->conceptToDeleteId = null;
    }

    public function confirmMassDeletion()
    {
        $this->dispatch('open-modal', 'confirm-mass-deletion');
    }

    public function massDelete()
    {
        try {
            // Solo eliminar conceptos que NO tengan pagos asociados para seguridad
            $count = 0;
            $concepts = PaymentConcept::withCount('payments')->get();
            
            foreach ($concepts as $c) {
                if ($c->payments_count === 0) {
                    $c->delete();
                    $count++;
                }
            }

            if ($count > 0) {
                session()->flash('message', "Se eliminaron $count conceptos sin uso.");
            } else {
                session()->flash('error', 'No se eliminaron conceptos porque todos tienen pagos asociados o no había registros.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error en borrado masivo: ' . $e->getMessage());
        }
        
        $this->dispatch('close-modal', 'confirm-mass-deletion');
    }
}