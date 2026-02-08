<?php

namespace App\Livewire\Calendar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Models\CourseSchedule;
use App\Models\AcademicEvent;
use Illuminate\Support\Facades\Cache;

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
    public $newEventType = 'academic'; 

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

        // Carga diferida solo al seleccionar el día (No cacheamos esto porque es interacción puntual)
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

        // Invalidar caché de eventos del mes afectado
        Cache::forget("calendar_events_{$this->currentYear}_{$this->currentMonth}");

        $this->closeEventModal();
        
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

    // --- LÓGICA DE DATOS OPTIMIZADA ---

    public function getCalendarDaysProperty()
    {
        // Cachear la estructura del mes completo para evitar recálculos en cada render
        $cacheKey = "calendar_structure_{$this->currentYear}_{$this->currentMonth}_" . 
                    ($this->showClasses ? '1' : '0') . 
                    ($this->showStartsEnds ? '1' : '0') . 
                    ($this->showAdmin ? '1' : '0');

        return Cache::remember($cacheKey, 60, function () { // Cache corto de 1 min para agilidad
            $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
            $daysInMonth = $date->daysInMonth;
            $startDayOfWeek = $date->dayOfWeek; 

            $calendar = [];
            for ($i = 0; $i < $startDayOfWeek; $i++) {
                $calendar[] = null;
            }

            // Precargar datos del mes en una sola consulta
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // 1. Eventos Académicos del Mes
            $eventsInMonth = AcademicEvent::whereBetween('date', [$monthStart, $monthEnd])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();

            // 2. Hitos del Sistema (Inicio/Fin)
            $startsInMonth = $this->showStartsEnds 
                ? CourseSchedule::whereBetween('start_date', [$monthStart, $monthEnd])->pluck('start_date')->toArray()
                : [];
            $endsInMonth = $this->showStartsEnds 
                ? CourseSchedule::whereBetween('end_date', [$monthStart, $monthEnd])->pluck('end_date')->toArray()
                : [];
            
            // 3. Clases (Días Activos) - Esto es lo más pesado, optimizamos
            // En lugar de query por día, traemos los rangos activos que se cruzan con este mes
            $activeSchedules = $this->showClasses 
                ? CourseSchedule::where('start_date', '<=', $monthEnd)
                    ->where('end_date', '>=', $monthStart)
                    ->get(['days_of_week', 'start_date', 'end_date']) // Solo columnas necesarias
                : collect();

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, $i);
                $dateStr = $currentDate->format('Y-m-d');
                
                // Verificaciones en memoria (mucho más rápido que DB)
                $hasEvents = in_array($dateStr, $eventsInMonth);
                
                $hasSystem = false;
                if ($this->showStartsEnds) {
                    // Check simple de strings en array
                    $hasSystem = collect($startsInMonth)->contains(fn($d) => substr($d, 0, 10) === $dateStr) ||
                                 collect($endsInMonth)->contains(fn($d) => substr($d, 0, 10) === $dateStr);
                }
                if ($this->showAdmin && $i === 28) $hasSystem = true;

                $hasClasses = false;
                if ($this->showClasses) {
                    $dayName = $this->translateDay($currentDate->dayName);
                    // Verificación en memoria sobre la colección precargada
                    $hasClasses = $activeSchedules->contains(function ($schedule) use ($currentDate, $dayName) {
                        $start = Carbon::parse($schedule->start_date);
                        $end = Carbon::parse($schedule->end_date);
                        return $currentDate->between($start, $end) && 
                               (is_array($schedule->days_of_week) 
                                ? in_array($dayName, $schedule->days_of_week) 
                                : str_contains($schedule->days_of_week, $dayName));
                    });
                }

                $calendar[] = [
                    'day' => $i,
                    'date' => $dateStr,
                    'isToday' => $currentDate->isToday(),
                    'hasClasses' => $hasClasses,
                    'hasEvents' => $hasEvents,
                    'hasSystem' => $hasSystem,
                ];
            }
            return $calendar;
        });
    }

    // --- HELPERS DE BÚSQUEDA ---

    private function getSectionsForDate(Carbon $date)
    {
        if (!$this->showClasses) return collect();

        $dayName = $this->translateDay($date->dayName); 

        // Eager Loading Selectivo
        return CourseSchedule::with([
                'module:id,name,course_id', 
                'module.course:id,name', 
                'teacher:id,name', 
                'classroom:id,name'
            ])
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->whereJsonContains('days_of_week', $dayName)
            ->orderBy('start_time')
            ->get();
    }

    private function getEventsForDate(Carbon $date)
    {
        // Cachear eventos por día (útil si muchos usuarios ven el mismo día hoy)
        return Cache::remember("events_day_" . $date->format('Y-m-d'), 300, function() use ($date) {
            return AcademicEvent::whereDate('date', $date->format('Y-m-d'))->get();
        });
    }

    private function getSystemEventsForDate(Carbon $date)
    {
        $events = [];
        $dateStr = $date->format('Y-m-d');

        if ($this->showStartsEnds) {
            $starts = CourseSchedule::with('module:id,name')->whereDate('start_date', $dateStr)->get(['id', 'module_id', 'section_name']);
            foreach($starts as $s) {
                $events[] = [
                    'title' => 'Inicio: ' . $s->section_name,
                    'description' => $s->module->name,
                    'type' => 'start',
                    'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'
                ];
            }

            $ends = CourseSchedule::with('module:id,name')->whereDate('end_date', $dateStr)->get(['id', 'module_id', 'section_name']);
            foreach($ends as $s) {
                $events[] = [
                    'title' => 'Fin: ' . $s->section_name,
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
        // Este método ya no se usa directamente en el loop principal optimizado,
        // pero se mantiene por compatibilidad si se llama individualmente.
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