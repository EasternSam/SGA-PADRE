<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountingAccount;

class ChartOfAccounts extends Component
{
    use WithPagination;

    public $search = '';
    public $type_filter = '';
    
    // Form properties
    public $showModal = false;
    public $account_id;
    public $parent_id = null;
    public $code;
    public $name;
    public $type;
    public $is_active = true;

    protected function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:accounting_accounts,code,' . $this->account_id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense,cost',
            'parent_id' => 'nullable|exists:accounting_accounts,id',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $account = AccountingAccount::findOrFail($id);
        
        $this->account_id = $account->id;
        $this->parent_id = $account->parent_id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->is_active = $account->is_active;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Si tiene un padre, auto-hereda el tipo del padre para evitar inconsistencias
        if ($this->parent_id) {
            $parent = AccountingAccount::find($this->parent_id);
            if ($parent) {
                $this->type = $parent->type;
            }
        }

        $data = [
            'parent_id' => $this->parent_id ?: null,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];

        if ($this->account_id) {
            AccountingAccount::findOrFail($this->account_id)->update($data);
            session()->flash('message', 'Cuenta contable actualizada exitosamente.');
        } else {
            AccountingAccount::create($data);
            session()->flash('message', 'Cuenta contable creada exitosamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $account = AccountingAccount::findOrFail($id);

        // Prevenir borrado si tiene subcuentas o transacciones
        if ($account->children()->count() > 0) {
            session()->flash('error', 'No se puede eliminar una cuenta que tiene subcuentas.');
            return;
        }

        if ($account->entryLines()->count() > 0) {
            session()->flash('error', 'No se puede eliminar una cuenta que tiene movimientos contables. Considere desactivarla.');
            return;
        }

        $account->delete();
        session()->flash('message', 'Cuenta eliminada exitosamente.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['account_id', 'parent_id', 'code', 'name', 'type', 'is_active']);
        $this->is_active = true; // default
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = AccountingAccount::with('parent')
                    ->orderBy('code', 'asc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }

        $accounts = $query->paginate(25);
        
        // Fetch all possible parents (excluding self to prevent circular references during edit)
        $parentQuery = AccountingAccount::orderBy('code', 'asc');
        if ($this->account_id) {
            $parentQuery->where('id', '!=', $this->account_id);
        }
        $potentialParents = $parentQuery->get();

        return view('livewire.admin.chart-of-accounts', [
            'accounts' => $accounts,
            'potentialParents' => $potentialParents
        ])->layout('layouts.dashboard');
    }
}
