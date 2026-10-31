import React, { useState } from 'react';
import CalendarMonthView from './CalendarMonthView';
import CalendarWeekView from './CalendarWeekView';
import CalendarDayView from './CalendarDayView';
import CalendarHeader from './CalendarHeader';
import CalendarEventModal from './CalendarEventModal';
import CalendarEventDetailModal from './CalendarEventDetailModal';

// Mock Events para Demos y Storybook
export const DEMO_EVENTS = [
    {
        id: 'demo_1',
        title: 'Inicio de Evaluaciones Parciales',
        description: 'Exámenes correspondientes al primer corte del cuatrimestre.',
        date: new Date().toISOString().split('T')[0],
        start_time: '08:00',
        end_time: '11:00',
        type: 'academic',
        classroom: 'Aula Magna A',
        teacher: 'Dr. Alejandro Morales',
        is_all_day: false,
        is_editable: true,
    },
    {
        id: 'demo_2',
        title: 'Reunión de Claustro y Coordinación',
        description: 'Alineación de metodologías didácticas.',
        date: new Date().toISOString().split('T')[0],
        start_time: '14:00',
        end_time: '16:00',
        type: 'administrative',
        classroom: 'Sala de Juntas',
        teacher: 'Decanato Académico',
        is_all_day: false,
        is_editable: true,
    },
    {
        id: 'demo_3',
        title: 'Feria de Tecnología e Innovación',
        description: 'Muestra de proyectos finales de estudiantes de término.',
        date: new Date(Date.now() + 86400000 * 2).toISOString().split('T')[0],
        start_time: '09:00',
        end_time: '17:00',
        type: 'extracurricular',
        classroom: 'Explanada Central',
        is_all_day: true,
        is_editable: true,
    },
    {
        id: 'demo_4',
        title: 'Programación Orientada a Objetos (Sección 02)',
        description: 'Clase presencial y práctica de laboratorio.',
        date: new Date(Date.now() + 86400000 * 1).toISOString().split('T')[0],
        start_time: '10:00',
        end_time: '12:00',
        type: 'class',
        classroom: 'Lab Informática 3',
        teacher: 'Prof. Carlos Rivera',
        is_all_day: false,
        is_editable: false,
    },
    {
        id: 'demo_5',
        title: 'Feriado Institucional',
        description: 'Día no laborable.',
        date: new Date(Date.now() + 86400000 * 4).toISOString().split('T')[0],
        start_time: null,
        end_time: null,
        type: 'holiday',
        is_all_day: true,
        is_editable: false,
    },
];

export function CalendarMonthViewDemo() {
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [selectedEvent, setSelectedEvent] = useState(null);

    return (
        <div className="w-full bg-gray-50 dark:bg-gray-950 p-2 sm:p-4 rounded-3xl">
            <CalendarMonthView
                selectedDate={selectedDate}
                events={DEMO_EVENTS}
                onSelectDate={setSelectedDate}
                onSelectEvent={setSelectedEvent}
            />
            {selectedEvent && (
                <CalendarEventDetailModal
                    event={selectedEvent}
                    onClose={() => setSelectedEvent(null)}
                />
            )}
        </div>
    );
}

export function CalendarWeekViewDemo() {
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [selectedEvent, setSelectedEvent] = useState(null);

    return (
        <div className="w-full bg-gray-50 dark:bg-gray-950 p-2 sm:p-4 rounded-3xl">
            <CalendarWeekView
                selectedDate={selectedDate}
                events={DEMO_EVENTS}
                onSelectDate={setSelectedDate}
                onSelectEvent={setSelectedEvent}
            />
            {selectedEvent && (
                <CalendarEventDetailModal
                    event={selectedEvent}
                    onClose={() => setSelectedEvent(null)}
                />
            )}
        </div>
    );
}

export function CalendarDayViewDemo() {
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [selectedEvent, setSelectedEvent] = useState(null);

    return (
        <div className="w-full bg-gray-50 dark:bg-gray-950 p-2 sm:p-4 rounded-3xl">
            <CalendarDayView
                selectedDate={selectedDate}
                events={DEMO_EVENTS}
                onSelectEvent={setSelectedEvent}
            />
            {selectedEvent && (
                <CalendarEventDetailModal
                    event={selectedEvent}
                    onClose={() => setSelectedEvent(null)}
                />
            )}
        </div>
    );
}
