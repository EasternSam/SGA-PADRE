import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { router } from '@inertiajs/react';
import { X, Calendar as CalendarIcon, Clock, Tag, AlignLeft, Check } from 'lucide-react';
import { EVENT_TYPES } from './config';

export default function CalendarEventModal({
    isOpen,
    onClose,
    initialDate,
    initialStartTime,
    editEvent,
}) {
    if (!isOpen) return null;

    const [title, setTitle] = useState('');
    const [date, setDate] = useState(initialDate || new Date().toISOString().split('T')[0]);
    const [type, setType] = useState('academic');
    const [startTime, setStartTime] = useState(initialStartTime || '09:00');
    const [endTime, setEndTime] = useState('11:00');
    const [description, setDescription] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (editEvent) {
            setTitle(editEvent.title || '');
            setDate(editEvent.date || '');
            setType(editEvent.type || 'academic');
            setStartTime(editEvent.start_time || '09:00');
            setEndTime(editEvent.end_time || '11:00');
            setDescription(editEvent.description || '');
        } else {
            setTitle('');
            setDate(initialDate || new Date().toISOString().split('T')[0]);
            setType('academic');
            setStartTime(initialStartTime || '09:00');
            setEndTime('11:00');
            setDescription('');
        }
    }, [editEvent, initialDate, initialStartTime]);

    const handleSubmit = (e) => {
        e.preventDefault();
        setError(null);
        setIsSubmitting(true);

        const payload = {
            title,
            date,
            type,
            start_time: startTime || null,
            end_time: endTime || null,
            description,
        };

        if (editEvent && editEvent.db_id) {
            router.put(`/admin/calendar/events/${editEvent.db_id}`, payload, {
                onSuccess: () => {
                    setIsSubmitting(false);
                    onClose();
                },
                onError: (errs) => {
                    setIsSubmitting(false);
                    setError(Object.values(errs)[0] || 'Error al guardar actividad');
                },
            });
        } else {
            router.post('/admin/calendar/events', payload, {
                onSuccess: () => {
                    setIsSubmitting(false);
                    onClose();
                },
                onError: (errs) => {
                    setIsSubmitting(false);
                    setError(Object.values(errs)[0] || 'Error al guardar actividad');
                },
            });
        }
    };

    return (
        createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" onClick={onClose}>
            <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95" onClick={(e) => e.stopPropagation()}>
                {/* Cabecera del Modal */}
                <div className="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            {editEvent ? 'Editar Actividad Académica' : 'Nueva Actividad o Hito'}
                        </h3>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-white rounded-lg transition"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Formulario */}
                <form onSubmit={handleSubmit} className="p-5 space-y-4">
                    {error && (
                        <div className="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                            {error}
                        </div>
                    )}

                    {/* Título */}
                    <div>
                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Título de la actividad *
                        </label>
                        <input
                            type="text"
                            required
                            placeholder="Ej: Inicio de Evaluaciones, Reunión de Claustro..."
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                            className="w-full px-3.5 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
                        />
                    </div>

                    {/* Tipo / Categoría */}
                    <div>
                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Tipo de Actividad *
                        </label>
                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            {['academic', 'administrative', 'holiday', 'extracurricular'].map((tKey) => {
                                const tInfo = EVENT_TYPES[tKey];
                                const isSelected = type === tKey;
                                return (
                                    <button
                                        key={tKey}
                                        type="button"
                                        onClick={() => setType(tKey)}
                                        className={`p-2 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition ${
                                            isSelected
                                                ? `${tInfo.badge} ring-2 ring-indigo-500/30`
                                                : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'
                                        }`}
                                    >
                                        <span className={`w-2 h-2 rounded-full ${tInfo.dot}`} />
                                        <span>{tInfo.label}</span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Fecha y Horas */}
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Fecha *
                            </label>
                            <input
                                type="date"
                                required
                                value={date}
                                onChange={(e) => setDate(e.target.value)}
                                className="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Hora Inicio
                            </label>
                            <input
                                type="time"
                                value={startTime}
                                onChange={(e) => setStartTime(e.target.value)}
                                className="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Hora Fin
                            </label>
                            <input
                                type="time"
                                value={endTime}
                                onChange={(e) => setEndTime(e.target.value)}
                                className="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    {/* Descripción */}
                    <div>
                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Descripción / Observaciones
                        </label>
                        <textarea
                            rows={3}
                            placeholder="Detalles sobre la actividad, instrucciones para docentes o estudiantes..."
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                            className="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    {/* Botones de Acción */}
                    <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className="inline-flex items-center gap-1.5 px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs transition disabled:opacity-50"
                        >
                            <Check className="w-3.5 h-3.5" />
                            <span>{isSubmitting ? 'Guardando...' : (editEvent ? 'Actualizar' : 'Agendar')}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>, document.body)
    );
}
