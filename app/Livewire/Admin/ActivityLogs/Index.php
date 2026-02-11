<?php

namespace App\Livewire\Admin\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $user_id = '';
    public $date_from = '';
    public $date_to = '';
    public $action_filter = '';

    // Modal de Detalles
    public $selectedLog = null;
    public $showDetailsModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'user_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount()
    {
        // Por defecto mostramos el mes actual
        if (!$this->date_from) {
            $this->date_from = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->date_to) {
            $this->date_to = now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewDetails($logId)
    {
        $this->selectedLog = ActivityLog::with('user')->find($logId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filtro de Búsqueda General
        if ($this->search) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($u) {
                      $u->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtro por Usuario
        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        // Filtro por Rango de Fechas
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }
        
        // Filtro por Tipo de Acción
        if ($this->action_filter) {
             $query->where('action', $this->action_filter);
        }

        $logs = $query->paginate(20);
        
        // Datos para los selectores
        $users = User::orderBy('name')->get();
        // Obtenemos acciones únicas para el filtro dropdown
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('livewire.admin.activity-logs.index', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions
        ])->layout('layouts.dashboard');
    }
}