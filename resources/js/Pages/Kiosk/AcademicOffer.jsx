import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function AcademicOffer({ student, courses = [] }) {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';

    const [selectedCourse, setSelectedCourse] = useState(courses[0] || null);

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
                    <div className="p-12 rounded-[24px] bg-[#F3F4F6] dark:bg-[#171717] text-center text-lg font-semibold">
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
                                    className={`w-full p-5 sm:p-6 rounded-2xl border-2 text-center transition-all active:scale-[0.99] bg-[#F3F4F6] dark:bg-[#171717] ${
                                        isSelected ? '' : 'border-transparent'
                                    }`}
                                >
                                    <div className="text-lg sm:text-xl font-bold">{c.name}</div>
                                </button>
                            );
                        })}
                    </div>
                )}

                {selectedCourse && (
                    <div className="pt-2 flex items-center justify-between gap-4">
                        <div className="text-sm font-semibold opacity-75">
                            {selectedCourse.duration_hours} • RD${' '}
                            {Number(selectedCourse.price || 0).toLocaleString('es-DO', {
                                minimumFractionDigits: 2,
                            })}
                        </div>

                        <Link
                            href="/admisiones"
                            style={{ backgroundColor: primaryColor, color: '#FFFFFF' }}
                            className="px-7 py-4 rounded-xl font-semibold text-base flex items-center gap-3 transition-all active:scale-95"
                        >
                            <span>Continuar</span>
                            <span>→</span>
                        </Link>
                    </div>
                )}
            </div>
        </KioskLayout>
    );
}
