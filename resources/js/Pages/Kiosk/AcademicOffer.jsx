import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function AcademicOffer({ student, courses = [] }) {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';
    const secondaryColor = branding?.secondary_color || '#6b289e';

    const [selectedCourse, setSelectedCourse] = useState(courses[0] || null);
    const [processing, setProcessing] = useState(false);

    const handleEnroll = () => {
        if (!selectedCourse || processing) return;

        if (student) {
            setProcessing(true);
            router.post(
                '/kiosk/academic-offer/enroll',
                { course_id: selectedCourse.id },
                { onFinish: () => setProcessing(false) }
            );
        } else {
            router.visit(`/kiosk/signup?course_id=${selectedCourse.id}`);
        }
    };

    return (
        <KioskLayout
            student={student}
            backUrl={student ? '/kiosk/dashboard' : '/kiosk/login'}
            showBack={true}
        >
            <Head title="Oferta Académica - Kiosco" />

            <div className="w-full max-w-3xl mx-auto space-y-6">
                <h1 className="text-2xl sm:text-3xl font-bold tracking-tight">
                    Selecciona un programa académico
                </h1>

                {courses.length === 0 ? (
                    <div className="p-12 rounded-[24px] kiosk-surface text-center text-lg font-semibold">
                        Sin programas disponibles
                    </div>
                ) : (
                    <div className="space-y-3 max-h-[58vh] overflow-y-auto pr-1">
                        {courses.map((c) => {
                            const isSelected = selectedCourse?.id === c.id;
                            return (
                                <button
                                    key={c.id}
                                    type="button"
                                    onClick={() => setSelectedCourse(c)}
                                    style={
                                        isSelected
                                            ? { borderColor: primaryColor, color: primaryColor }
                                            : undefined
                                    }
                                    className={`w-full p-5 sm:p-6 rounded-2xl text-center transition-all active:scale-[0.99] kiosk-card-interactive ${
                                        isSelected ? 'ring-2 ring-[var(--kiosk-primary)]/20' : ''
                                    }`}
                                >
                                    <div className="text-lg sm:text-xl font-bold">{c.name}</div>
                                </button>
                            );
                        })}
                    </div>
                )}

                {selectedCourse && (
                    <div className="pt-2 flex flex-wrap items-center justify-between gap-4">
                        <div className="text-sm font-semibold opacity-80">
                            {selectedCourse.duration_hours} • RD${' '}
                            {Number(selectedCourse.price || 0).toLocaleString('es-DO', {
                                minimumFractionDigits: 2,
                            })}
                        </div>

                        <button
                            type="button"
                            disabled={processing}
                            onClick={handleEnroll}
                            style={{
                                background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
                                color: '#FFFFFF',
                            }}
                            className="px-7 py-4 rounded-xl font-semibold text-base flex items-center gap-3 transition-all active:scale-95"
                        >
                            <span>
                                {processing
                                    ? 'Procesando...'
                                    : student
                                    ? 'Inscribirme ahora'
                                    : 'Continuar'}
                            </span>
                            <span>→</span>
                        </button>
                    </div>
                )}
            </div>
        </KioskLayout>
    );
}
