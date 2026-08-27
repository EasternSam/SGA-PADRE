<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;

class Attendance extends Component
{
    public $summary = [];
    public $recentAbsences = [];
    public $totalPresent = 0;
    public $totalAbsent = 0;
    public $totalExcused = 0;
    public $totalLate = 0;
    public $attendanceRate = 0;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->student) {
            return redirect()->route('kiosk.login');
        }

        $student = $user->student;
        $activeYear = AcademicYear::where('status', 'active')->first();

        if (!$activeYear) return;

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereHas('section', fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get();

        $this->totalPresent = $records->where('status', 'present')->count();
        $this->totalAbsent = $records->where('status', 'absent')->count();
        $this->totalExcused = $records->where('status', 'excused')->count();
        $this->totalLate = $records->where('status', 'late')->count();

        $total = $records->count();
        $this->attendanceRate = $total > 0
            ? round((($this->totalPresent + $this->totalExcused + $this->totalLate) / $total) * 100, 1)
            : 100;

        // Últimas 10 ausencias/tardanzas
        $this->recentAbsences = StudentAttendance::where('student_id', $student->id)
            ->whereIn('status', ['absent', 'late', 'excused'])
            ->orderByDesc('date')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'date' => $r->date?->format('d/m/Y') ?? '',
                'day' => $r->date?->translatedFormat('l') ?? '',
                'status' => $r->status,
                'status_label' => match($r->status) {
                    'absent' => 'Ausente',
                    'late' => 'Tardanza',
                    'excused' => 'Excusada',
                    default => $r->status,
                },
            ])
            ->toArray();
    }

    public function goBack()
    {
        return $this->redirect(route('kiosk.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.attendance')->layout('layouts.kiosk');
    }
}
