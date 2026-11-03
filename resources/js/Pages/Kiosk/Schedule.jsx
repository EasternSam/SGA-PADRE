import React, { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Schedule({ student, schedules = [] }) {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';
    const secondaryColor = branding?.secondary_color || '#6b289e';

    const [selectedSubjectId, setSelectedSubjectId] = useState(schedules[0]?.id || null);
    const activeSubject = schedules.find((s) => s.id === selectedSubjectId) || schedules[0] || null;

    return (
        <KioskLayout student={student} backUrl="/kiosk/dashboard" showBack={true}>
            <Head title="Mi Horario - Kiosco" />

            <div className="w-full max-w-3xl mx-auto space-y-6">
                <h1 className="text-2xl sm:text-3xl font-bold tracking-tight">
                    Mi Horario
                </h1>

                {schedules.length === 0 ? (
                    <div className="p-12 rounded-[24px] kiosk-surface text-center text-lg font-semibold">
                        Sin asignaturas programadas
                    </div>
                ) : (
                    <div className="space-y-3">
                        {schedules.map((s) => {
                            const isSelected = activeSubject?.id === s.id;
                            return (
                                <button
                                    key={s.id}
                                    type="button"
                                    onClick={() => setSelectedSubjectId(s.id)}
                                    style={
                                        isSelected
                                            ? { borderColor: primaryColor, color: primaryColor }
                                            : undefined
                                    }
                                    className={`w-full p-5 sm:p-6 rounded-2xl text-left transition-all active:scale-[0.99] flex flex-col sm:flex-row sm:items-center justify-between gap-2 kiosk-card-interactive ${
                                        isSelected ? 'ring-2 ring-[var(--kiosk-primary)]/20' : ''
                                    }`}
                                >
                                    <span className="text-lg sm:text-xl font-bold">{s.subject_name}</span>
                                    <span className="text-sm font-mono font-semibold opacity-80">
                                        {s.days} • {s.time} • {s.classroom}
                                    </span>
                                </button>
                            );
                        })}

                        <div className="pt-4 flex justify-end">
                            <button
                                type="button"
                                onClick={() => window.print()}
                                style={{
                                    background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
                                    color: '#FFFFFF',
                                }}
                                className="px-7 py-4 rounded-xl font-semibold text-base flex items-center gap-3 transition-all active:scale-95"
                            >
                                <span>Imprimir</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </KioskLayout>
    );
}
