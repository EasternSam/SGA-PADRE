import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Portal({ applicant, admission, courses = [] }) {
    const { flash = {} } = usePage().props;
    const [step, setStep] = useState(admission ? 'status' : 'form');

    const { data, setData, post, processing, errors } = useForm({
        first_name: admission?.first_name || '',
        last_name: admission?.last_name || '',
        identification_id: admission?.identification_id || '',
        phone: admission?.phone || '',
        birth_date: admission?.birth_date || '',
        address: admission?.address || '',
        course_id: admission?.course?.id || (courses[0]?.id || ''),
        previous_school: admission?.previous_school || '',
        previous_gpa: admission?.previous_gpa || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/portal-aspirante', {
            onSuccess: () => setStep('status'),
        });
    };

    const getStatusBadge = (status) => {
        switch (status) {
            case 'approved':
                return {
                    label: 'Aprobada e Inscrita',
                    class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
                    icon: 'fa-solid fa-circle-check text-emerald-500',
                };
            case 'rejected':
                return {
                    label: 'Rechazada / Requiere Cambios',
                    class: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30',
                    icon: 'fa-solid fa-circle-xmark text-rose-500',
                };
            default:
                return {
                    label: 'En Proceso de Revisión',
                    class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30',
                    icon: 'fa-solid fa-hourglass-half text-amber-500',
                };
        }
    };

    const statusBadge = getStatusBadge(admission?.status);

    return (
        <AuthenticatedLayout title="Portal del Aspirante">
            <Head title="Portal de Admisión - SGA" />

            <div className="max-w-5xl mx-auto space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-sga-primary dark:text-blue-400 uppercase tracking-wider">
                            <i className="fa-solid fa-graduation-cap"></i> Proceso de Admisiones
                        </div>
                        <h1 className="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                            Bienvenido, {applicant?.name}
                        </h1>
                        
                    </div>

                    {admission && (
                        <div className="flex gap-2">
                            <button
                                onClick={() => setStep('status')}
                                className={`px-4 py-2 rounded-xl text-sm font-semibold transition-all ${
                                    step === 'status'
                                        ? 'bg-sga-primary text-white shadow-md'
                                        : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="fa-solid fa-timeline mr-1.5"></i> Estatus
                            </button>
                            <button
                                onClick={() => setStep('form')}
                                className={`px-4 py-2 rounded-xl text-sm font-semibold transition-all ${
                                    step === 'form'
                                        ? 'bg-sga-primary text-white shadow-md'
                                        : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="fa-solid fa-pen-to-square mr-1.5"></i> Expediente
                            </button>
                        </div>
                    )}
                </div>

                {/* STATUS VIEW */}
                {step === 'status' && admission && (
                    <div className="space-y-6">
                        {/* Overall Status Card */}
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm">
                            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-white/5">
                                <div className="flex items-center gap-4">
                                    <div className="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-2xl">
                                        <i className={statusBadge.icon}></i>
                                    </div>
                                    <div>
                                        <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${statusBadge.class}`}>
                                            {statusBadge.label}
                                        </span>
                                        <h3 className="text-xl font-bold text-slate-800 dark:text-white mt-1">
                                            {admission.course?.name || 'Carrera Universitaria'}
                                        </h3>
                                        <p className="text-xs text-slate-500 font-mono mt-0.5">
                                            Código: {admission.course?.code || 'N/A'} • Enviada el {admission.created_at}
                                        </p>
                                    </div>
                                </div>

                                <div className="text-right">
                                    <div className="text-xs text-slate-400">ID de Solicitud</div>
                                    <div className="text-lg font-mono font-bold text-slate-800 dark:text-white">
                                        ADM-{String(admission.id).padStart(5, '0')}
                                    </div>
                                </div>
                            </div>

                            {/* Documents Progress Checklist */}
                            <div className="pt-6">
                                <h4 className="text-sm font-bold text-slate-800 dark:text-white mb-4">
                                    Documentación Requerida
                                </h4>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                    {[
                                        { title: 'Acta de Nacimiento', icon: 'fa-solid fa-file-lines' },
                                        { title: 'Cédula / Identidad', icon: 'fa-solid fa-id-card' },
                                        { title: 'Récord de Bachillerato', icon: 'fa-solid fa-scroll' },
                                        { title: 'Fotografía 2x2', icon: 'fa-solid fa-image' },
                                    ].map((doc, idx) => (
                                        <div key={idx} className="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/5 flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <i className={`${doc.icon} text-slate-400 text-lg`}></i>
                                                <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">{doc.title}</span>
                                            </div>
                                            <i className="fa-solid fa-check text-emerald-500 text-sm"></i>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Personal info summary */}
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <div className="text-xs text-slate-400 uppercase font-semibold">Postulante</div>
                                <div className="text-base font-bold text-slate-800 dark:text-white mt-1">
                                    {admission.first_name} {admission.last_name}
                                </div>
                                <div className="text-xs text-slate-500 font-mono mt-0.5">{admission.identification_id}</div>
                            </div>
                            <div>
                                <div className="text-xs text-slate-400 uppercase font-semibold">Contacto</div>
                                <div className="text-base font-bold text-slate-800 dark:text-white mt-1">{admission.phone}</div>
                                <div className="text-xs text-slate-500 mt-0.5">{admission.address}</div>
                            </div>
                            <div>
                                <div className="text-xs text-slate-400 uppercase font-semibold">Procedencia Escolar</div>
                                <div className="text-base font-bold text-slate-800 dark:text-white mt-1">{admission.previous_school}</div>
                                <div className="text-xs text-slate-500 mt-0.5">Promedio: {admission.previous_gpa || 'N/A'}%</div>
                            </div>
                        </div>
                    </div>
                )}

                {/* APPLICATION FORM VIEW */}
                {(step === 'form' || !admission) && (
                    <form onSubmit={handleSubmit} className="bg-white dark:bg-[#161622] rounded-2xl p-8 border border-slate-200 dark:border-white/5 shadow-sm space-y-6">
                        <div className="border-b border-slate-100 dark:border-white/5 pb-4">
                            <h3 className="text-xl font-bold text-slate-800 dark:text-white">
                                {admission ? 'Actualizar Información de Solicitud' : 'Formulario de Admisión e Ingreso'}
                            </h3>
                            <p className="text-slate-500 text-xs mt-1">
                                Completa todos los campos con exactitud de acuerdo con tus documentos de identidad oficial.
                            </p>
                        </div>

                        {/* Grid Form */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Nombres
                                </label>
                                <input
                                    type="text"
                                    value={data.first_name}
                                    onChange={e => setData('first_name', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Apellidos
                                </label>
                                <input
                                    type="text"
                                    value={data.last_name}
                                    onChange={e => setData('last_name', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Cédula o Pasaporte
                                </label>
                                <input
                                    type="text"
                                    value={data.identification_id}
                                    onChange={e => setData('identification_id', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Teléfono / WhatsApp
                                </label>
                                <input
                                    type="text"
                                    value={data.phone}
                                    onChange={e => setData('phone', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Fecha de Nacimiento
                                </label>
                                <input
                                    type="date"
                                    value={data.birth_date}
                                    onChange={e => setData('birth_date', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Carrera o Programa de Interés
                                </label>
                                <select
                                    value={data.course_id}
                                    onChange={e => setData('course_id', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                >
                                    {courses.map(c => (
                                        <option key={c.id} value={c.id} className="text-black">
                                            {c.name} ({c.code})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="md:col-span-2">
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Dirección de Residencia
                                </label>
                                <input
                                    type="text"
                                    value={data.address}
                                    onChange={e => setData('address', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Liceo / Colegio de Procedencia
                                </label>
                                <input
                                    type="text"
                                    value={data.previous_school}
                                    onChange={e => setData('previous_school', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                    Promedio Académico de Bachillerato (%)
                                </label>
                                <input
                                    type="number"
                                    step="0.1"
                                    value={data.previous_gpa}
                                    onChange={e => setData('previous_gpa', e.target.value)}
                                    placeholder="Ej: 88.5"
                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white focus:outline-none focus:border-sga-primary text-sm"
                                />
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-white/5">
                            {admission && (
                                <button
                                    type="button"
                                    onClick={() => setStep('status')}
                                    className="px-6 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:text-white text-sm font-semibold"
                                >
                                    Cancelar
                                </button>
                            )}
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-8 py-3 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-900/30 transition-all flex items-center gap-2"
                            >
                                {processing ? (
                                    <>
                                        <i className="fa-solid fa-circle-notch fa-spin"></i>
                                        <span>Enviando...</span>
                                    </>
                                ) : (
                                    <>
                                        <i className="fa-solid fa-paper-plane"></i>
                                        <span>{admission ? 'Guardar Cambios' : 'Registrar Solicitud'}</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
