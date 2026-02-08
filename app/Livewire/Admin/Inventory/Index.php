<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\InventoryItem;
use App\Models\Classroom;
use Illuminate\Validation\Rule;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $locationFilter = ''; 

    // Modal Properties
    public $showModal = false; // Mantenemos por compatibilidad, pero usaremos eventos
    public $itemId = null;
    
    // Form Fields
    public $name, $serial_number, $asset_tag, $category, $status = 'Operativo';
    public $classroom_id, $notes, $purchase_date, $cost;

    public function render()
    {
        $query = InventoryItem::with('classroom')
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('serial_number', 'like', '%'.$this->search.'%')
                        ->orWhere('asset_tag', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter));

        if ($this->locationFilter === 'warehouse') {
            $query->whereNull('classroom_id');
        } elseif ($this->locationFilter) {
            $query->where('classroom_id', $this->locationFilter);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total' => InventoryItem::count(),
            'warehouse' => InventoryItem::whereNull('classroom_id')->count(),
            'defective' => InventoryItem::whereIn('status', ['Defectuoso', 'En Reparación'])->count(),
            'value' => InventoryItem::sum('cost'),
        ];

        return view('livewire.admin.inventory.index', [
            'items' => $items,
            'stats' => $stats,
            'classrooms' => Classroom::orderBy('name')->get(),
            'categories' => InventoryItem::distinct('category')->pluck('category'),
        ]);
    }

    public function create()
    {
        $this->resetInput();
        // Disparar evento para abrir modal
        $this->dispatch('open-modal', 'inventory-modal');
    }

    public function edit($id)
    {
        $this->resetInput();
        $item = InventoryItem::findOrFail($id);
        
        $this->itemId = $id;
        $this->name = $item->name;
        $this->serial_number = $item->serial_number;
        $this->asset_tag = $item->asset_tag;
        $this->category = $item->category;
        $this->status = $item->status;
        $this->classroom_id = $item->classroom_id;
        $this->notes = $item->notes;
        $this->purchase_date = $item->purchase_date?->format('Y-m-d');
        $this->cost = $item->cost;

        // Disparar evento para abrir modal
        $this->dispatch('open-modal', 'inventory-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:Operativo,Defectuoso,En Reparación,Obsoleto',
            'serial_number' => ['nullable', 'string', Rule::unique('inventory_items')->ignore($this->itemId)],
            'asset_tag' => ['nullable', 'string', Rule::unique('inventory_items')->ignore($this->itemId)],
            'classroom_id' => 'nullable|exists:classrooms,id',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'serial_number' => $this->serial_number,
            'asset_tag' => $this->asset_tag,
            'category' => $this->category,
            'status' => $this->status,
            'classroom_id' => $this->classroom_id ?: null,
            'notes' => $this->notes,
            'purchase_date' => $this->purchase_date,
            'cost' => $this->cost,
        ];

        InventoryItem::updateOrCreate(['id' => $this->itemId], $data);

        session()->flash('message', 'Inventario actualizado correctamente.');
        
        // Disparar evento para cerrar modal
        $this->dispatch('close-modal', 'inventory-modal');
        $this->resetInput();
    }
    
    public function closeModal()
    {
        $this->dispatch('close-modal', 'inventory-modal');
        $this->resetInput();
    }

    public function delete($id)
    {
        InventoryItem::destroy($id);
        session()->flash('message', 'Ítem eliminado del inventario.');
    }

    private function resetInput()
    {
        $this->reset(['itemId', 'name', 'serial_number', 'asset_tag', 'category', 'status', 'classroom_id', 'notes', 'purchase_date', 'cost']);
        $this->status = 'Operativo';
    }
}