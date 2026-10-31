import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Activity, TrendingUp, Calendar, DollarSign } from 'lucide-react';
import CalendarHeader from '@/components/application/calendar/CalendarHeader';
import CalendarMonthView from '@/components/application/calendar/CalendarMonthView';
import CalendarWeekView from '@/components/application/calendar/CalendarWeekView';
import CalendarDayView from '@/components/application/calendar/CalendarDayView';
import CalendarEventModal from '@/components/application/calendar/CalendarEventModal';
import CalendarEventDetailModal from '@/components/application/calendar/CalendarEventDetailModal';

export default function CalendarIndex({
    events = [],
    currentView = 'month',
    currentDate,
    classrooms = [],
    teachers = [],
    filters = { classes: true, events: true, system: true, classroom_id: '', teacher_id: '' },
    meta = {},
}) {
    const [view, setView] = useState(currentView);
    const [selectedDate, setSelectedDate] = useState(currentDate || new Date().toISOString().split('T')[0]);
    const [localFilters, setLocalFilters] = useState(filters);

    // Modales
    const [isNewModalOpen, setIsNewModalOpen] = useState(false);
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [editingEvent, setEditingEvent] = useState(null);
    const [modalSlotTime, setModalSlotTime] = useState(null);

    // Enviar query al backend para refrescar rango de datos
    const reloadCalendarData = (newDate, newView, newFilters) => {
        router.get(
            '/admin/calendar',
            {
                date: newDate,
                view: newView,
                classes: newFilters.classes ? 1 : 0,
                events: newFilters.events ? 1 : 0,
                system: newFilters.system ? 1 : 0,
                classroom_id: newFilters.classroom_id || '',
                teacher_id: newFilters.teacher_id || '',
            },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['events', 'currentDate', 'currentView', 'filters'],
            }
        );
    };

    // Cambiar vista (Mes, Semana, Día)
    const handleViewChange = (newView) => {
        setView(newView);
        reloadCalendarData(selectedDate, newView, localFilters);
    };

    // Navegar anterior o siguiente
    const handleNavigate = (direction) => {
        const d = new Date(selectedDate);
        if (view === 'month') {
            d.setUTCMonth(d.getUTCMonth() + (direction === 'next' ? 1 : -1));
        } else if (view === 'week') {
            d.setUTCDate(d.getUTCDate() + (direction === 'next' ? 7 : -7));
        } else {
            d.setUTCDate(d.getUTCDate() + (direction === 'next' ? 1 : -1));
        }

        const newDateStr = d.toISOString().split('T')[0];
        setSelectedDate(newDateStr);
        reloadCalendarData(newDateStr, view, localFilters);
    };

    // Botón Hoy
    const handleToday = () => {
        const todayStr = new Date().toISOString().split('T')[0];
        setSelectedDate(todayStr);
        reloadCalendarData(todayStr, view, localFilters);
    };

    // Toggle de filtros de capas
    const handleToggleFilter = (filterKey) => {
        const updated = { ...localFilters, [filterKey]: !localFilters[filterKey] };
        setLocalFilters(updated);
        reloadCalendarData(selectedDate, view, updated);
    };

    // Filtro por Aula
    const handleClassroomChange = (val) => {
        const updated = { ...localFilters, classroom_id: val };
        setLocalFilters(updated);
        reloadCalendarData(selectedDate, view, updated);
    };

    // Filtro por Profesor
    const handleTeacherChange = (val) => {
        const updated = { ...localFilters, teacher_id: val };
        setLocalFilters(updated);
        reloadCalendarData(selectedDate, view, updated);
    };

    // Abrir creación con fecha/hora específica
    const handleSlotClick = (dateStr, timeStr) => {
        setSelectedDate(dateStr);
        setModalSlotTime(timeStr);
        setEditingEvent(null);
        setIsNewModalOpen(true);
    };

    // Zoom desde Mes o Semana a Día
    const handleViewDay = (dateStr) => {
        setSelectedDate(dateStr);
        setView('day');
        reloadCalendarData(dateStr, 'day', localFilters);
    };

    // Eliminar evento
    const handleDeleteEvent = (event) => {
        if (!event.db_id) return;
        if (confirm(`¿Estás seguro de eliminar la actividad "${event.title}"?`)) {
            router.delete(`/admin/calendar/events/${event.db_id}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AuthenticatedLayout title="Calendario Académico">
            <Head title={meta.title || 'Calendario Académico'} />

            <div className="space-y-5 p-4 sm:p-6 lg:p-8 max-w-[1600px] mx-auto animate-in fade-in duration-300">
                {/* Header de la Página */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            {meta.title || 'Calendario Académico'}
                        </h1>
                        
                    </div>
                </div>

                {/* Barra de Controles y Filtros del Calendario */}
                <CalendarHeader
                    currentView={view}
                    onViewChange={handleViewChange}
                    selectedDate={selectedDate}
                    onNavigate={handleNavigate}
                    onToday={handleToday}
                    filters={localFilters}
                    onToggleFilter={handleToggleFilter}
                    classrooms={classrooms}
                    teachers={teachers}
                    onClassroomChange={handleClassroomChange}
                    onTeacherChange={handleTeacherChange}
                    onOpenNewEvent={() => {
                        setEditingEvent(null);
                        setModalSlotTime('09:00');
                        setIsNewModalOpen(true);
                    }}
                />

                {/* Vistas Conmutables de Untitled UI */}
                <div className="transition-opacity duration-200">
                    {view === 'month' && (
                        <CalendarMonthView
                            selectedDate={selectedDate}
                            events={events}
                            onSelectDate={setSelectedDate}
                            onSelectEvent={setSelectedEvent}
                            onViewDay={handleViewDay}
                        />
                    )}

                    {view === 'week' && (
                        <CalendarWeekView
                            selectedDate={selectedDate}
                            events={events}
                            onSelectDate={setSelectedDate}
                            onSelectEvent={setSelectedEvent}
                            onSlotClick={handleSlotClick}
                            onViewDay={handleViewDay}
                        />
                    )}

                    {view === 'day' && (
                        <CalendarDayView
                            selectedDate={selectedDate}
                            events={events}
                            onSelectEvent={setSelectedEvent}
                            onSlotClick={handleSlotClick}
                            onEditEvent={(evt) => {
                                setEditingEvent(evt);
                                setIsNewModalOpen(true);
                            }}
                            onDeleteEvent={handleDeleteEvent}
                        />
                    )}
                </div>
            </div>

            {/* Modal de Detalle de Evento */}
            <CalendarEventDetailModal
                event={selectedEvent}
                onClose={() => setSelectedEvent(null)}
                onEdit={(evt) => {
                    setEditingEvent(evt);
                    setIsNewModalOpen(true);
                }}
                onDelete={handleDeleteEvent}
            />

            {/* Modal de Crear / Editar Actividad */}
            <CalendarEventModal
                isOpen={isNewModalOpen}
                onClose={() => {
                    setIsNewModalOpen(false);
                    setEditingEvent(null);
                }}
                initialDate={selectedDate}
                initialStartTime={modalSlotTime}
                editEvent={editingEvent}
            />
        
            </AuthenticatedLayout>
    );
}
