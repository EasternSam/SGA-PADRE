import React from 'react';
import { createPortal } from 'react-dom';
import { X, Calendar as CalendarIcon, Clock, MapPin, User, BookOpen, Edit2, Trash2 } from 'lucide-react';
import { getEventStyle, MONTHS_ES } from './config';

export default function CalendarEventDetailModal({
    event,
    onClose,
    onEdit,
    onDelete,
}) {
    if (!event) return null;

    const style = getEventStyle(event.type);
    const d = new Date(event.date);
    const dateFormatted = `${d.getUTCDate()} de ${MONTHS_ES[d.getUTCMonth()]}, ${d.getUTCFullYear()}`;

    return (
        createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" onClick={onClose}>
            <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95" onClick={(e) => e.stopPropagation()}>
                {/* Header con Badge de Tipo */}
                <div className="p-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-3">
                    <div className="space-y-1.5">
                        <span className={`inline-flex items-center gap-1.5 text-xs px-2.5 py-0.5 rounded-full font-semibold border ${style.badge}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${style.dot}`} />
                            {style.label}
                        </span>
                        <h3 className="text-base font-bold text-gray-900 dark:text-white leading-snug">
                            {event.title}
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

                {/* Contenido / Metadatos */}
                <div className="p-5 space-y-4 text-xs text-gray-600 dark:text-gray-300">
                    <div className="space-y-2.5 bg-gray-50 dark:bg-gray-800/50 p-3.5 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center gap-2.5">
                            <CalendarIcon className="w-4 h-4 text-gray-400 shrink-0" />
                            <span className="font-semibold text-gray-900 dark:text-white">{dateFormatted}</span>
                        </div>

                        {event.start_time && (
                            <div className="flex items-center gap-2.5">
                                <Clock className="w-4 h-4 text-gray-400 shrink-0" />
                                <span>{event.start_time} - {event.end_time || 'Sin hora fin'}</span>
                            </div>
                        )}

                        {event.classroom && (
                            <div className="flex items-center gap-2.5">
                                <MapPin className="w-4 h-4 text-gray-400 shrink-0" />
                                <span>Aula / Espacio: <strong className="text-gray-900 dark:text-white">{event.classroom}</strong></span>
                            </div>
                        )}

                        {event.teacher && (
                            <div className="flex items-center gap-2.5">
                                <User className="w-4 h-4 text-gray-400 shrink-0" />
                                <span>Docente: <strong className="text-gray-900 dark:text-white">{event.teacher}</strong></span>
                            </div>
                        )}

                        {event.modality && (
                            <div className="flex items-center gap-2.5">
                                <BookOpen className="w-4 h-4 text-gray-400 shrink-0" />
                                <span>Modalidad: <strong className="text-gray-900 dark:text-white">{event.modality}</strong></span>
                            </div>
                        )}
                    </div>

                    {event.description && (
                        <div>
                            <span className="font-semibold text-gray-700 dark:text-gray-300 block mb-1">
                                Detalle / Descripción:
                            </span>
                            <p className="text-gray-600 dark:text-gray-400 leading-relaxed bg-white dark:bg-gray-800/30 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                                {event.description}
                            </p>
                        </div>
                    )}
                </div>

                {/* Footer y Acciones */}
                <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/30">
                    <div>
                        {event.is_editable && (
                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={() => {
                                        onClose();
                                        onEdit && onEdit(event);
                                    }}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-950/40 rounded-lg transition"
                                >
                                    <Edit2 className="w-3.5 h-3.5" />
                                    Editar
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        onClose();
                                        onDelete && onDelete(event);
                                    }}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 rounded-lg transition"
                                >
                                    <Trash2 className="w-3.5 h-3.5" />
                                    Eliminar
                                </button>
                            </div>
                        )}
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="px-4 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>, document.body)
    );
}
