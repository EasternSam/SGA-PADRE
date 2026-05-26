<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\SchoolSchedule;
use App\Models\SectionSubject;
use App\Models\TimeBlock;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.kiosk')]
class Schedule extends Component
{
    public $scheduleByDay = [];
    public $sectionName = '';
    public $gradeLevelName = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->student) {
            return redirect()->route('kiosk.login');
        }

        $student = $user->student;
        $this->sectionName = $student->section?->name ?? '';
        $this->gradeLevelName = $student->gradeLevel?->name ?? '';

        if (!$student->section_id) return;

        // Buscar horarios de la sección del estudiante
        $schedules = SchoolSchedule::where('section_id', $student->section_id)
            ->with(['sectionSubject.subject', 'sectionSubject.teacher', 'timeBlock'])
            ->orderBy('day_of_week')
            ->get();

        $daysOrder = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $grouped = [];

        foreach ($daysOrder as $day) {
            $grouped[$day] = [];
        }

        foreach ($schedules as $s) {
            $day = $s->day_of_week ?? 'Lunes';
            $grouped[$day][] = [
                'subject' => $s->sectionSubject?->subject?->name ?? 'Asignatura',
                'teacher' => $s->sectionSubject?->teacher
                    ? ($s->sectionSubject->teacher->first_name . ' ' . $s->sectionSubject->teacher->last_name)
                    : 'Por asignar',
                'time_start' => $s->timeBlock?->start_time ? \Carbon\Carbon::parse($s->timeBlock->start_time)->format('h:i A') : '--',
                'time_end' => $s->timeBlock?->end_time ? \Carbon\Carbon::parse($s->timeBlock->end_time)->format('h:i A') : '--',
            ];
        }

        // Ordenar cada día por hora
        foreach ($grouped as $day => &$items) {
            usort($items, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
        }

        $this->scheduleByDay = $grouped;
    }

    public function goBack()
    {
        return $this->redirectRoute('kiosk.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.schedule');
    }
}
