/**
 * Untitled UI - Calendar Configuration
 * Paletas de color, categorías de eventos y constantes para vistas Mes, Semana y Día.
 */

export const EVENT_TYPES = {
    academic: {
        id: 'academic',
        label: 'Académico',
        badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
        dot: 'bg-blue-500',
        pill: 'bg-blue-500/10 text-blue-700 border-l-4 border-blue-500 dark:bg-blue-950/40 dark:text-blue-200',
        card: 'bg-blue-50/80 hover:bg-blue-100/90 border-blue-200 text-blue-900 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-200',
    },
    administrative: {
        id: 'administrative',
        label: 'Administrativo',
        badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
        dot: 'bg-amber-500',
        pill: 'bg-amber-500/10 text-amber-800 border-l-4 border-amber-500 dark:bg-amber-950/40 dark:text-amber-200',
        card: 'bg-amber-50/80 hover:bg-amber-100/90 border-amber-200 text-amber-900 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-200',
    },
    holiday: {
        id: 'holiday',
        label: 'Feriado / Festivo',
        badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
        dot: 'bg-rose-500',
        pill: 'bg-rose-500/10 text-rose-700 border-l-4 border-rose-500 dark:bg-rose-950/40 dark:text-rose-200',
        card: 'bg-rose-50/80 hover:bg-rose-100/90 border-rose-200 text-rose-900 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-200',
    },
    extracurricular: {
        id: 'extracurricular',
        label: 'Extracurricular',
        badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
        dot: 'bg-emerald-500',
        pill: 'bg-emerald-500/10 text-emerald-700 border-l-4 border-emerald-500 dark:bg-emerald-950/40 dark:text-emerald-200',
        card: 'bg-emerald-50/80 hover:bg-emerald-100/90 border-emerald-200 text-emerald-900 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-200',
    },
    class: {
        id: 'class',
        label: 'Docencia / Clases',
        badge: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800',
        dot: 'bg-purple-500',
        pill: 'bg-purple-500/10 text-purple-700 border-l-4 border-purple-500 dark:bg-purple-950/40 dark:text-purple-200',
        card: 'bg-purple-50/80 hover:bg-purple-100/90 border-purple-200 text-purple-900 dark:bg-purple-950/40 dark:border-purple-800 dark:text-purple-200',
    },
    start: {
        id: 'start',
        label: 'Inicio de Curso',
        badge: 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800',
        dot: 'bg-teal-500',
        pill: 'bg-teal-500/10 text-teal-700 border-l-4 border-teal-500 dark:bg-teal-950/40 dark:text-teal-200',
        card: 'bg-teal-50/80 hover:bg-teal-100/90 border-teal-200 text-teal-900 dark:bg-teal-950/40 dark:border-teal-800 dark:text-teal-200',
    },
    end: {
        id: 'end',
        label: 'Cierre de Curso',
        badge: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-800',
        dot: 'bg-orange-500',
        pill: 'bg-orange-500/10 text-orange-700 border-l-4 border-orange-500 dark:bg-orange-950/40 dark:text-orange-200',
        card: 'bg-orange-50/80 hover:bg-orange-100/90 border-orange-200 text-orange-900 dark:bg-orange-950/40 dark:border-orange-800 dark:text-orange-200',
    },
};

export const WEEKDAYS_ES = [
    { short: 'Lun', long: 'Lunes', index: 1 },
    { short: 'Mar', long: 'Martes', index: 2 },
    { short: 'Mié', long: 'Miércoles', index: 3 },
    { short: 'Jue', long: 'Jueves', index: 4 },
    { short: 'Vie', long: 'Viernes', index: 5 },
    { short: 'Sáb', long: 'Sábado', index: 6 },
    { short: 'Dom', long: 'Domingo', index: 0 },
];

export const MONTHS_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

export const CALENDAR_START_HOUR = 7;  // 07:00 AM
export const CALENDAR_END_HOUR = 22;   // 10:00 PM
export const CALENDAR_TOTAL_HOURS = CALENDAR_END_HOUR - CALENDAR_START_HOUR;

export function getEventStyle(type) {
    return EVENT_TYPES[type] || EVENT_TYPES.academic;
}
