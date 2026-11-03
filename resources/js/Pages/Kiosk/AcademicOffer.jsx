import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function AcademicOffer({ student, courses = [] }) {
    const [search, setSearch] = useState('');
    const [selectedType, setSelectedType] = useState('all'); // 'all' | 'degree' | 'course'
    const [selectedCourse, setSelectedCourse] = useState(courses[0] || null);
    const [acceptedInfo, setAcceptedInfo] = useState(true);

    const filtered = courses.filter((c) => {
        const matchSearch =
            (c.name || '').toLowerCase().includes(search.toLowerCase()) ||
            (c.code || '').toLowerCase().includes(search.toLowerCase());
        const matchType =
            selectedType === 'all' ||
            (selectedType === 'degree' && c.program_type === 'degree') ||
            (selectedType === 'course' && c.program_type !== 'degree');
        return matchSearch && matchType;
    });

    const activeCourse =
        filtered.find((c) => c.id === selectedCourse?.id) || filtered[0] || null;

    return (
        <KioskLayout
            title="Explorar Oferta Académica"
            student={student}
            backUrl={student ? '/kiosk/dashboard' : '/kiosk/login'}
            showBack={true}
        >
            <Head title="Oferta Académica - Kiosco" />

            <div className="max-w-5xl mx-auto w-full py-2 space-y-6">
                {/* Header & Category Filter Tiles (Reference Image 4 left screen + Image 5 right screen) */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-[#111827] dark:text-white tracking-tight">
                            Selecciona el programa académico de tu interés
                        </h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Toca cualquier carrera o curso para ver sus requisitos, duración e inversión.
                        </p>
                    </div>

                    {/* Filter Pills */}
                    <div className="flex gap-2">
                        {[
                            { id: 'all', label: 'Todos' },
                            { id: 'degree', label: 'Carreras' },
                            { id: 'course', label: 'Cursos / Técnico' },
                        ].map((tab) => (
                            <button
                                key={tab.id}
                                type="button"
                                onClick={() => setSelectedType(tab.id)}
                                className={`px-4 py-2.5 rounded-xl font-bold text-xs md:text-sm border-2 transition-all active:scale-95 ${
                                    selectedType === tab.id
                                        ? 'bg-[#F3F4F6] dark:bg-slate-800 border-[#3B7A57] text-[#3B7A57] dark:text-emerald-400'
                                        : 'bg-[#F3F4F6] dark:bg-slate-800/60 border-transparent text-slate-600 dark:text-slate-300'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Search Input */}
                <div className="relative">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Filtrar por nombre o código del programa..."
                        className="w-full px-5 py-4 rounded-2xl bg-[#F3F4F6] dark:bg-slate-800 border-2 border-transparent focus:border-[#3B7A57] focus:bg-white dark:focus:bg-slate-900 text-base font-semibold text-[#111827] dark:text-white placeholder-slate-400 focus:outline-none transition-all"
                    />
                    {search && (
                        <button
                            type="button"
                            onClick={() => setSearch('')}
                            className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 font-bold text-lg"
                        >
                            ×
                        </button>
                    )}
                </div>

                {/* Two-Column Interactive Browser (Image 5 left & right screens combined) */}
                {filtered.length === 0 ? (
                    <div className="p-12 rounded-3xl bg-[#F3F4F6] dark:bg-slate-800/80 text-center">
                        <div className="text-xl font-bold text-[#111827] dark:text-white">
                            No se encontraron programas con ese filtro
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
                        {/* Left: Stacked Selectable Program Blocks (Exact style of Image 4 left & Image 5 right) */}
                        <div className="lg:col-span-6 space-y-3 max-h-[480px] overflow-y-auto pr-1">
                            {filtered.map((c) => {
                                const isSelected = activeCourse?.id === c.id;
                                return (
                                    <button
                                        key={c.id}
                                        type="button"
                                        onClick={() => setSelectedCourse(c)}
                                        className={`w-full p-5 rounded-2xl border-2 text-center transition-all active:scale-[0.99] ${
                                            isSelected
                                                ? 'bg-[#F3F4F6] dark:bg-slate-800 border-[#3B7A57] text-[#3B7A57] dark:text-emerald-400 shadow-xs'
                                                : 'bg-[#F3F4F6] dark:bg-slate-800/70 border-transparent text-[#111827] dark:text-white hover:bg-[#EAECEF]'
                                        }`}
                                    >
                                        <div className="text-lg md:text-xl font-bold leading-snug">
                                            {c.name}
                                        </div>
                                        <div className="text-xs font-medium opacity-75 mt-1">
                                            {c.code} • {c.program_type === 'degree' ? 'Carrera' : 'Curso / Diplomado'} •{' '}
                                            RD$ {Number(c.price || 0).toLocaleString('es-DO', { minimumFractionDigits: 0 })}
                                        </div>
                                    </button>
                                );
                            })}
                        </div>

                        {/* Right: Program Rules, Investment & Action Card (Inspired by Image 5 left & right) */}
                        <div className="lg:col-span-6 rounded-3xl bg-[#F8FAFC] dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 p-7 space-y-5 shadow-xs">
                            {activeCourse && (
                                <>
                                    <div>
                                        <span className="px-3 py-1 rounded-full bg-[#E6F2F0] dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-300 text-xs font-mono font-bold">
                                            {activeCourse.code}
                                        </span>
                                        <h2 className="text-2xl font-bold text-[#111827] dark:text-white mt-2">
                                            {activeCourse.name}
                                        </h2>
                                    </div>

                                    <div className="space-y-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                        <p>
                                            • <strong>Modalidad y Duración:</strong> {activeCourse.duration_hours} con acompañamiento práctico y acceso a plataforma virtual.
                                        </p>
                                        <p>
                                            • <strong>Inversión Mensual / Cuota:</strong>{' '}
                                            <span className="font-bold text-[#166534] dark:text-emerald-400">
                                                RD$ {Number(activeCourse.price || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                            </span>
                                        </p>
                                        <p>
                                            • <strong>Requisitos de Admisión:</strong> Copia de cédula o acta de nacimiento, certificado oficial de estudios y formulario de admisión digital.
                                        </p>
                                    </div>

                                    {/* Checkbox Agreement Box (Exact match to Image 5 bottom-left green bordered checkbox!) */}
                                    <button
                                        type="button"
                                        onClick={() => setAcceptedInfo(!acceptedInfo)}
                                        className="w-full p-3.5 rounded-xl border-2 border-[#3B7A57] bg-white dark:bg-slate-900 flex items-center gap-3.5 text-left transition-all active:scale-[0.99]"
                                    >
                                        <span
                                            className={`w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shrink-0 ${
                                                acceptedInfo ? 'bg-[#3B7A57]' : 'bg-slate-200 dark:bg-slate-700'
                                            }`}
                                        >
                                            ✓
                                        </span>
                                        <span className="text-xs font-bold text-[#3B7A57] dark:text-emerald-400 leading-snug">
                                            He revisado la información académica, duración y requisitos de admisión de este programa
                                        </span>
                                    </button>

                                    {/* Bottom Right Primary Action (Image 5 bottom-right green button) */}
                                    <div className="pt-2 flex flex-wrap items-center justify-between gap-3">
                                        <button
                                            type="button"
                                            onClick={() => window.print()}
                                            className="px-5 py-3.5 rounded-xl bg-[#F3F4F6] hover:bg-[#E5E7EB] dark:bg-slate-700 text-xs font-bold text-[#111827] dark:text-white transition-all active:scale-95"
                                        >
                                            Imprimir Ficha
                                        </button>

                                        <Link
                                            href="/admisiones"
                                            className="px-7 py-4 rounded-xl bg-[#3B7A57] hover:bg-[#2E6346] text-white font-bold text-sm flex items-center gap-3 transition-all active:scale-95 shadow-sm"
                                        >
                                            <span>Solicitar Admisión</span>
                                            <span>→</span>
                                        </Link>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </KioskLayout>
    );
}
