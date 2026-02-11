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
    public $user_id = ''; // Filtro por usuario específico (dropdown)
    public $date_from = '';
    public $date_to = '';
    
    // Eliminamos action_filter temporalmente si no se va a usar

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
        // Establecer fechas por defecto solo si no vienen en la URL
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

        // 1. Filtro de Búsqueda Inteligente
        // Permite buscar por texto general O datos de estudiante (Matrícula/Cédula)
        if ($this->search) {
            $term = $this->search;
            $query->where(function($q) use ($term) {
                $q->where('description', 'like', '%'.$term.'%')
                  ->orWhere('action', 'like', '%'.$term.'%')
                  ->orWhere('ip_address', 'like', '%'.$term.'%')
                  // Busca en el usuario (Nombre/Email)
                  ->orWhereHas('user', function($u) use ($term) {
                      $u->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        // Y si es estudiante, busca por sus datos específicos
                        ->orWhereHas('student', function($s) use ($term) {
                             $s->where('student_code', 'like', '%'.$term.'%')
                               ->orWhere('cedula', 'like', '%'.$term.'%');
                        });
                  });
            });
        }

        // 2. Filtro de Dropdown de Usuario (Solo Admin/Staff)
        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        // 3. Filtro de Fechas
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }
        
        $logs = $query->paginate(20);
        
        // --- LOGICA DEL DROPDOWN FILTRADO ---
        // Cargamos SOLO usuarios que NO sean estudiantes ni solicitantes.
        // Esto reduce la lista de miles a solo los administrativos/docentes (quizás 20-50 personas).
        $users = User::whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Estudiante', 'Solicitante']);
            })
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('livewire.admin.activity-logs.index', [
            'logs' => $logs,
            'users' => $users, 
            'actions' => [],
        ])->layout('layouts.dashboard');
    }
}