<?php

namespace App\Livewire\Calendar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Models\CourseSchedule;
use App\Models\AcademicEvent;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $selectedDayData = []; 

    // Filtros
    public $showClasses = true;
    public $showStartsEnds = true;
    public $showAdmin = true;

    // --- PROPIEDADES PARA NUEVO EVENTO ---
    public $showEventModal = false;
    public $newEventTitle = '';
    public $newEventDescription = '';
    public $newEventDate = '';
    public $newEventStartTime = '';
    public $newEventEndTime = '';
    public $newEventType = 'academic'; // academic, administrative, holiday

    public function mount()
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->selectedDate = null; 
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->selectedDate = null;
    }

    public function selectDay($day)
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, $day);
        $dateString = $date->format('Y-m-d');
        $this->selectedDate = $dateString;

        $this->selectedDayData = [
            'date_human' => ucfirst($date->isoFormat('dddd D [de] MMMM, YYYY')),
            'sections' => $this->getSectionsForDate($date),
            'events' => $this->getEventsForDate($date),
            'system_events' => $this->getSystemEventsForDate($date),
        ];
    }

    // --- MÉTODOS PARA GESTIÓN DE EVENTOS ---

    public function openEventModal()
    {
        $this->resetEventForm();
        // Si hay un día seleccionado, pre-llenar la fecha
        if ($this->selectedDate) {
            $this->newEventDate = $this->selectedDate;
        } else {
            $this->newEventDate = Carbon::now()->format('Y-m-d');
        }
        $this->showEventModal = true;
    }

    public function closeEventModal()
    {
        $this->showEventModal = false;
        $this->resetEventForm();
    }

    public function saveEvent()
    {
        $this->validate([
            'newEventTitle' => 'required|string|max:255',
            'newEventDate' => 'required|date',
            'newEventType' => 'required|in:academic,administrative,holiday,extracurricular',
            'newEventDescription' => 'nullable|string',
            'newEventStartTime' => 'nullable|date_format:H:i',
            'newEventEndTime' => 'nullable|date_format:H:i|after:newEventStartTime',
        ]);

        AcademicEvent::create([
            'title' => $this->newEventTitle,
            'description' => $this->newEventDescription,
            'date' => $this->newEventDate,
            'start_time' => $this->newEventStartTime ? $this->newEventDate . ' ' . $this->newEventStartTime : null,
            'end_time' => $this->newEventEndTime ? $this->newEventDate . ' ' . $this->newEventEndTime : null,
            'type' => $this->newEventType,
        ]);

        $this->closeEventModal();
        
        // Refrescar la selección si el evento creado es para el día seleccionado actualmente
        if ($this->selectedDate && $this->newEventDate == $this->selectedDate) {
            $day = Carbon::parse($this->selectedDate)->day;
            $this->selectDay($day);
        }

        session()->flash('message', 'Actividad agendada correctamente.');
    }

    private function resetEventForm()
    {
        $this->newEventTitle = '';
        $this->newEventDescription = '';
        $this->newEventDate = '';
        $this->newEventStartTime = '';
        $this->newEventEndTime = '';
        $this->newEventType = 'academic';
        $this->resetValidation();
    }

    // --- LÓGICA DE DATOS ---

    public function getCalendarDaysProperty()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $date->daysInMonth;
        $startDayOfWeek = $date->dayOfWeek; 

        $calendar = [];

        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $calendar[] = null;
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, $i);
            
            $hasClasses = $this->showClasses ? $this->hasSectionsOnDate($currentDate) : false;
            $hasEvents = $this->hasEventsOnDate($currentDate);
            $hasSystem = $this->showStartsEnds ? $this->hasSystemEventsOnDate($currentDate) : false;

            $calendar[] = [
                'day' => $i,
                'date' => $currentDate->format('Y-m-d'),
                'isToday' => $currentDate->isToday(),
                'hasClasses' => $hasClasses,
                'hasEvents' => $hasEvents,
                'hasSystem' => $hasSystem,
            ];
        }

        return $calendar;
    }

    // --- HELPERS DE BÚSQUEDA ---

    private function getSectionsForDate(Carbon $date)
    {
        if (!$this->showClasses) return collect();

        $dayName = $this->translateDay($date->dayName); 

        return CourseSchedule::with(['module.course', 'teacher', 'classroom'])
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->whereJsonContains('days_of_week', $dayName)
            ->orderBy('start_time')
            ->get();
    }

    private function getEventsForDate(Carbon $date)
    {
        return AcademicEvent::whereDate('date', $date->format('Y-m-d'))->get();
    }

    private function getSystemEventsForDate(Carbon $date)
    {
        $events = [];

        if ($this->showStartsEnds) {
            $starts = CourseSchedule::with('module')->whereDate('start_date', $date->format('Y-m-d'))->get();
            foreach($starts as $s) {
                $events[] = [
                    'title' => 'Inicio de Clases: ' . $s->section_name,
                    'description' => $s->module->name,
                    'type' => 'start',
                    'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'
                ];
            }

            $ends = CourseSchedule::with('module')->whereDate('end_date', $date->format('Y-m-d'))->get();
            foreach($ends as $s) {
                $events[] = [
                    'title' => 'Fin de Clases: ' . $s->section_name,
                    'description' => $s->module->name,
                    'type' => 'end',
                    'color' => 'bg-rose-100 text-rose-700 border-rose-200'
                ];
            }
        }

        if ($this->showAdmin && $date->day === 28) {
            $events[] = [
                'title' => 'Corte Administrativo',
                'description' => 'Generación automática de mensualidades.',
                'type' => 'finance',
                'color' => 'bg-amber-100 text-amber-700 border-amber-200'
            ];
        }

        return collect($events);
    }

    private function hasSectionsOnDate(Carbon $date)
    {
        $dayName = $this->translateDay($date->dayName);
        return CourseSchedule::where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->whereJsonContains('days_of_week', $dayName)
            ->exists();
    }

    private function hasEventsOnDate(Carbon $date)
    {
        return AcademicEvent::whereDate('date', $date->format('Y-m-d'))->exists();
    }

    private function hasSystemEventsOnDate(Carbon $date)
    {
        if (CourseSchedule::whereDate('start_date', $date->format('Y-m-d'))->exists()) return true;
        if (CourseSchedule::whereDate('end_date', $date->format('Y-m-d'))->exists()) return true;
        if ($this->showAdmin && $date->day === 28) return true;

        return false;
    }

    private function translateDay($englishDay)
    {
        $map = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
            'lunes' => 'Lunes', 'martes' => 'Martes', 'miércoles' => 'Miércoles',
            'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sábado' => 'Sábado', 'domingo' => 'Domingo'
        ];
        return $map[ucfirst($englishDay)] ?? $englishDay;
    }

    public function render()
    {
        return view('livewire.calendar.index', [
            'calendarDays' => $this->getCalendarDaysProperty()
        ]);
    }
}