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
    public $selectedDayData = []; // Detalles del día seleccionado

    // Filtros
    public $showClasses = true;
    public $showStartsEnds = true;
    public $showAdmin = true;

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
        $this->selectedDate = null; // Limpiar selección al cambiar mes
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
        // Construir fecha completa
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, $day);
        $dateString = $date->format('Y-m-d');
        $this->selectedDate = $dateString;

        // Recopilar TODA la información para ese día específico
        $this->selectedDayData = [
            'date_human' => ucfirst($date->isoFormat('dddd D [de] MMMM, YYYY')),
            'sections' => $this->getSectionsForDate($date),
            'events' => $this->getEventsForDate($date),
            'system_events' => $this->getSystemEventsForDate($date),
        ];
    }

    // --- LÓGICA DE DATOS ---

    /**
     * Construye la estructura del calendario (días vacíos al inicio + días del mes)
     */
    public function getCalendarDaysProperty()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $date->daysInMonth;
        
        // Determinar en qué día de la semana cae el 1ro (0=Domingo, 1=Lunes, etc.)
        // Carbon dayOfWeek: 0 (Sunday) - 6 (Saturday).
        $startDayOfWeek = $date->dayOfWeek; 

        $calendar = [];

        // Relleno inicial (días vacíos)
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $calendar[] = null;
        }

        // Días reales
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, $i);
            
            // Pre-cálculo ligero para indicadores (puntos de colores en el calendario)
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

        // Traducir día de Carbon a español para comparar con la BD
        $dayName = $this->translateDay($date->dayName); // 'Lunes', 'Martes'...

        // Buscar secciones activas en este rango de fecha y que coincidan con el día de la semana
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

        // 1. Inicios de Sección
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

            // 2. Fines de Sección
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

        // 3. Generación de Deudas (Ejemplo: Día 28 de cada mes)
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

    // --- HELPERS BOOLEANOS PARA LA VISTA MENSUAL (Optimización) ---

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
        // Check starts
        if (CourseSchedule::whereDate('start_date', $date->format('Y-m-d'))->exists()) return true;
        // Check ends
        if (CourseSchedule::whereDate('end_date', $date->format('Y-m-d'))->exists()) return true;
        // Check admin days
        if ($this->showAdmin && $date->day === 28) return true;

        return false;
    }

    private function translateDay($englishDay)
    {
        $map = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
            // Carbon puede devolver en español si está configurado, aseguramos mapeo
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