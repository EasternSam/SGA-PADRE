<?php

namespace App\Livewire\Admin\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    // Propiedades para filtros ligeros
    public $actions = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'user_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount()
    {
        // Establecer fechas por defecto solo si no vienen en la URL
        if (!$this->date_from) {
            $this->date_from = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->date_to) {
            $this->date_to = now()->endOfMonth()->format('Y-m-d');
        }

        // Cargar acciones únicas de forma eficiente (usando caché si fuera necesario, aquí directo pero optimizado)
        // Limitamos a las ultimas 1000 acciones para no escanear toda la tabla si es gigante
        $this->actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->limit(50) 
            ->pluck('action')
            ->toArray();
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
        $query = ActivityLog::with('user') // Eager loading esencial
            ->orderBy('created_at', 'desc');

        // Filtro de Búsqueda General
        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function($q) use ($term) {
                $q->where('description', 'like', $term)
                  ->orWhere('action', 'like', $term)
                  ->orWhere('ip_address', 'like', $term);
                  // Eliminamos la búsqueda por relación de usuario en el cuadro general para mejorar rendimiento
                  // si hay muchos registros. Se puede buscar por usuario específico en el filtro dedicado.
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

        // Paginación estándar
        $logs = $query->paginate(20);
        
        // Carga de usuarios optimizada: Solo cargamos usuarios que tengan logs recientes o admins
        // O simplemente limitamos la lista para el select. 
        // Si son miles, mejor usar un input de búsqueda, pero por ahora limitamos a 100.
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->limit(200) // Limite de seguridad
            ->get();

        return view('livewire.admin.activity-logs.index', [
            'logs' => $logs,
            'users' => $users,
        ])->layout('layouts.dashboard');
    }
}