import React, { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Grades({ student, grades = [], gpa = 88.5 }) {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';

    const [selectedGradeId, setSelectedGradeId] = useState(grades[0]?.id || null);

    return (
        <KioskLayout student={student} backUrl="/kiosk/dashboard" showBack={true}>
            <Head title="Calificaciones - Kiosco" />

            <div className="w-full max-w-3xl mx-auto space-y-6">
                <div className="flex items-baseline justify-between">
                    <h1 className="text-2xl sm:text-3xl font-bold tracking-tight">
                        Calificaciones
                    </h1>
                    <div className="text-xl sm:text-2xl font-black" style={{ color: primaryColor }}>
                        Índice: {gpa}
                    </div>
                </div>

                {grades.length === 0 ? (
                    <div className="p-12 rounded-[24px] bg-[#F3F4F6] dark:bg-[#171717] text-center text-lg font-semibold">
                        Sin calificaciones publicadas
                    </div>
                ) : (
                    <div className="space-y-3">
                        {grades.map((g) => {
                            const isSelected = selectedGradeId === g.id;
                            return (
                                <div
                                    key={g.id}
                                    onClick={() => setSelectedGradeId(g.id)}
                                    style={
                                        isSelected
                                            ? { borderColor: primaryColor, color: primaryColor }
                                            : undefined
                                    }
                                    className={`p-5 sm:p-6 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between gap-4 bg-[#F3F4F6] dark:bg-[#171717] ${
                                        isSelected ? '' : 'border-transparent'
                                    }`}
                                >
                                    <span className="text-lg sm:text-xl font-bold">{g.subject}</span>
                                    <span className="text-2xl font-mono font-black">{g.score}</span>
                                </div>
                            );
                        })}

                        <div className="pt-4 flex justify-end">
                            <button
                                type="button"
                                onClick={() => window.print()}
                                style={{ backgroundColor: primaryColor, color: '#FFFFFF' }}
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
