import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function AcademicOffer({ courses = [] }) {
    const [search, setSearch] = useState('');
    const [selectedType, setSelectedType] = useState('all');

    const filtered = courses.filter(c => {
        const matchSearch = (c.name || '').toLowerCase().includes(search.toLowerCase()) ||
                            (c.code || '').toLowerCase().includes(search.toLowerCase());
        const matchType = selectedType === 'all' || c.program_type === selectedType;
        return matchSearch && matchType;
    });

    return (
        <KioskLayout title="Oferta Académica" showBack={true}>
            <Head title="Oferta Académica - Kiosco" />

            <div className="max-w-5xl mx-auto w-full space-y-6">
                {/* Search & Filters */}
                <div className="flex flex-col sm:flex-row gap-4 p-6 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl">
                    <div className="relative flex-1">
                        <i className="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Buscar carrera, técnico o diplomado..."
                            className="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-white/5 border border-white/10 text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 text-base"
                        />
                    </div>

                    <div className="flex gap-2">
                        <button
                            onClick={() => setSelectedType('all')}
                            className={`px-5 py-3 rounded-2xl font-bold text-sm border transition-all ${
                                selectedType === 'all'
                                    ? 'bg-blue-600 border-blue-500 text-white'
                                    : 'bg-white/5 border-white/10 text-slate-400 hover:text-white'
                            }`}
                        >
                            Todos
                        </button>
                        <button
                            onClick={() => setSelectedType('degree')}
                            className={`px-5 py-3 rounded-2xl font-bold text-sm border transition-all ${
                                selectedType === 'degree'
                                    ? 'bg-blue-600 border-blue-500 text-white'
                                    : 'bg-white/5 border-white/10 text-slate-400 hover:text-white'
                            }`}
                        >
                            Carreras
                        </button>
                    </div>
                </div>

                {/* Offer Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {filtered.length === 0 ? (
                        <div className="md:col-span-2 p-12 text-center rounded-3xl bg-slate-900/60 border border-white/10 text-slate-400">
                            <i className="fa-solid fa-graduation-cap text-5xl text-slate-500 mb-3"></i>
                            <div className="text-lg font-semibold text-white">No se encontraron programas con ese criterio</div>
                        </div>
                    ) : (
                        filtered.map(c => (
                            <div
                                key={c.id}
                                className="p-6 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl flex flex-col justify-between hover:border-purple-500/40 transition-all shadow-xl group"
                            >
                                <div>
                                    <div className="flex items-center justify-between mb-3">
                                        <span className="px-3 py-1 rounded-full text-xs font-mono font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                            {c.code}
                                        </span>
                                        <span className="text-xs text-slate-400 capitalize">
                                            {c.program_type === 'degree' ? 'Carrera Tecnológica' : 'Educación Continuada'}
                                        </span>
                                    </div>
                                    <h3 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors">
                                        {c.name}
                                    </h3>
                                </div>

                                <div className="mt-6 pt-4 border-t border-white/5 flex items-center justify-between">
                                    <div className="text-xs text-slate-400">
                                        <i className="fa-regular fa-clock mr-1 text-purple-400"></i>
                                        <span>{c.duration_hours ? `${c.duration_hours} horas` : '2 años / 6 cuatrimestres'}</span>
                                    </div>
                                    <div className="text-right">
                                        <div className="text-base font-mono font-bold text-emerald-400">
                                            RD$ {Number(c.price || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </KioskLayout>
    );
}
