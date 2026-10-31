import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Editor({ template }) {
    const { data, setData, post, processing, errors } = useForm({
        id: template?.id || null,
        name: template?.name || 'Nuevo Certificado Académico',
        is_active: template?.is_active ?? true,
        layout_data: template?.layout_data || {
            orientation: 'landscape',
            title: 'REPÚBLICA DOMINICANA',
            subtitle: 'INSTITUTO TECNOLÓGICO DE LAS AMÉRICAS',
            headline: 'CONFIERE EL PRESENTE TÍTULO A',
            student_placeholder: 'NOMBRE DEL GRADUANDO',
            body: 'Por haber completado satisfactoriamente el plan de estudios correspondiente a la carrera de:',
            course_placeholder: 'TECNOLOGÍA EN DESARROLLO DE SOFTWARE',
            date_text: 'Dado en Santo Domingo Este, a los 30 días de Septiembre de 2026.',
            signatures: [
                { name: 'Rector / Director General', role: 'Firma y Sello Oficial' },
                { name: 'Director de Registro Académico', role: 'Certificación Académica' },
            ],
        },
    });

    const handleLayoutChange = (key, val) => {
        setData('layout_data', {
            ...data.layout_data,
            [key]: val,
        });
    };

    const handleSignatureChange = (index, field, val) => {
        const sigs = [...(data.layout_data.signatures || [])];
        sigs[index] = { ...sigs[index], [field]: val };
        handleLayoutChange('signatures', sigs);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/certificate-editor/save');
    };

    return (
        <AuthenticatedLayout title="Diseñador de Certificados">
            <Head title="Editor de Certificado - SGA" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/certificates"
                            className="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 hover:bg-slate-200 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all"
                        >
                            <i className="fa-solid fa-arrow-left"></i>
                        </Link>
                        <div>
                            <h1 className="text-xl font-bold text-slate-800 dark:text-white">
                                {data.name}
                            </h1>
                            
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => window.print()}
                            className="px-4 py-2 rounded-xl bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-700 dark:text-slate-300 font-semibold text-xs transition-all flex items-center gap-1.5"
                        >
                            <i className="fa-solid fa-eye"></i>
                            <span>Previsualizar</span>
                        </button>
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={processing}
                            className="px-6 py-2 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2"
                        >
                            {processing ? (
                                <>
                                    <i className="fa-solid fa-circle-notch fa-spin"></i>
                                    <span>Guardando...</span>
                                </>
                            ) : (
                                <>
                                    <i className="fa-solid fa-floppy-disk"></i>
                                    <span>Guardar Plantilla</span>
                                </>
                            )}
                        </button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {/* Left Form Controls */}
                    <div className="lg:col-span-5 bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm space-y-4">
                        <h3 className="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-white/5 pb-2">
                            Parámetros del Documento
                        </h3>

                        <div>
                            <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nombre de la Plantilla</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                className="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white font-semibold"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Encabezado País</label>
                            <input
                                type="text"
                                value={data.layout_data.title}
                                onChange={e => handleLayoutChange('title', e.target.value)}
                                className="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nombre Institución</label>
                            <input
                                type="text"
                                value={data.layout_data.subtitle}
                                onChange={e => handleLayoutChange('subtitle', e.target.value)}
                                className="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Frase de Otorgamiento</label>
                            <input
                                type="text"
                                value={data.layout_data.headline}
                                onChange={e => handleLayoutChange('headline', e.target.value)}
                                className="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Cuerpo / Descripción</label>
                            <textarea
                                rows="3"
                                value={data.layout_data.body}
                                onChange={e => handleLayoutChange('body', e.target.value)}
                                className="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white"
                            ></textarea>
                        </div>

                        <div>
                            <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Texto de Fecha y Lugar</label>
                            <input
                                type="text"
                                value={data.layout_data.date_text}
                                onChange={e => handleLayoutChange('date_text', e.target.value)}
                                className="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs text-slate-800 dark:text-white"
                            />
                        </div>

                        <div className="pt-2">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={e => setData('is_active', e.target.checked)}
                                    className="w-4 h-4 rounded text-sga-primary"
                                />
                                <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">Plantilla activa para emisión</span>
                            </label>
                        </div>
                    </div>

                    {/* Right Live Canvas Preview */}
                    <div className="lg:col-span-7">
                        <div className="bg-amber-50/80 dark:bg-slate-900 border-8 border-double border-amber-600/40 rounded-3xl p-10 shadow-2xl relative overflow-hidden text-center text-slate-800 dark:text-slate-200">
                            {/* Watermark Seal */}
                            <div className="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
                                <i className="fa-solid fa-graduation-cap text-[280px]"></i>
                            </div>

                            {/* Header */}
                            <div className="relative z-10 space-y-1">
                                <div className="w-16 h-16 mx-auto mb-2 rounded-full bg-amber-600/10 border border-amber-600/30 flex items-center justify-center text-2xl text-amber-700 dark:text-amber-400">
                                    <i className="fa-solid fa-award"></i>
                                </div>
                                <h4 className="text-xs font-serif uppercase tracking-widest text-slate-600 dark:text-slate-400">
                                    {data.layout_data.title}
                                </h4>
                                <h3 className="text-base font-serif font-black tracking-wide text-sga-primary dark:text-blue-400">
                                    {data.layout_data.subtitle}
                                </h3>
                                <p className="text-xs font-serif italic text-slate-500 mt-2">
                                    {data.layout_data.headline}
                                </p>
                            </div>

                            {/* Student Name */}
                            <div className="relative z-10 my-8">
                                <h2 className="text-2xl md:text-3xl font-serif font-black text-amber-900 dark:text-amber-200 tracking-wider uppercase border-b-2 border-amber-600/30 pb-2 inline-block px-8">
                                    {data.layout_data.student_placeholder}
                                </h2>
                            </div>

                            {/* Body & Course */}
                            <div className="relative z-10 space-y-3 max-w-lg mx-auto">
                                <p className="text-xs font-serif leading-relaxed text-slate-600 dark:text-slate-300">
                                    {data.layout_data.body}
                                </p>
                                <div className="text-lg font-serif font-bold text-sga-primary dark:text-blue-300 uppercase tracking-wide">
                                    {data.layout_data.course_placeholder}
                                </div>
                                <p className="text-[11px] font-serif italic text-slate-500 mt-4">
                                    {data.layout_data.date_text}
                                </p>
                            </div>

                            {/* Signatures */}
                            <div className="relative z-10 mt-12 pt-6 grid grid-cols-2 gap-8 max-w-md mx-auto">
                                {(data.layout_data.signatures || []).map((sig, idx) => (
                                    <div key={idx} className="space-y-1">
                                        <div className="w-full border-t border-slate-400 dark:border-slate-600 pt-1"></div>
                                        <div className="text-[11px] font-bold text-slate-800 dark:text-white">{sig.name}</div>
                                        <div className="text-[9px] text-slate-500 uppercase">{sig.role}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
