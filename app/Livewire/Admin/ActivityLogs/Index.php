<?php

namespace App\Livewire\Admin\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $user_id = ''; // Filtro por usuario específico (dropdown)
    public $date_from = '';
    public $date_to = '';
    
    // Modal de Detalles
    public $selectedLog = null;
    public $showDetailsModal = false;

    // Propiedades para filtros
    public $actions = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'user_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount()
    {
        if (!$this->date_from) {
            $this->date_from = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->date_to) {
            $this->date_to = now()->endOfMonth()->format('Y-m-d');
        }

        $this->actions = []; 
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewDetails($logId)
    {
        $this->selectedLog = Activity::with('causer')->find($logId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        $query = Activity::with('causer')
            ->orderBy('created_at', 'desc');

        // 1. Filtro de Búsqueda ESTRICTO
        if ($this->search) {
            $term = $this->search;
            $query->where(function($q) use ($term) {
                // Búsqueda general en la descripción o acción del log
                $q->where('description', 'like', '%'.$term.'%')
                  ->orWhere('event', 'like', '%'.$term.'%')
                  
                  // Búsqueda en Usuarios (ADMINISTRATIVOS) por nombre o email
                  ->orWhereHas('causer', function($u) use ($term) {
                      $u->where(function($qu) use ($term) {
                          $qu->where('name', 'like', '%'.$term.'%')
                             ->orWhere('email', 'like', '%'.$term.'%');
                      });
                  });
            });
        }

        // 2. Filtro de Dropdown de Usuario
        if ($this->user_id) {
            $query->where('causer_id', $this->user_id);
        }

        // 3. Filtro de Fechas
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }
        
        $logs = $query->paginate(20);
        
        // --- LOGICA DE USUARIOS (Solo Personal Administrativo/Docente) ---
        // Excluimos explícitamente Estudiantes y Solicitantes para que la lista no sea gigante.
        $users = User::whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Estudiante', 'Solicitante']);
            })
            ->orderBy('name')
            ->select('id', 'name', 'email')
            ->get();

        return view('livewire.admin.activity-logs.index', [
            'logs' => $logs,
            'users' => $users, 
            'actions' => [],
        ])->layout('layouts.dashboard');
    }
}