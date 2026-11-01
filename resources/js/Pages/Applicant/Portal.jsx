import React, { useEffect, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { getCsrfHeaders } from '@/components/pos/utils';

const REQUIRED_DOCUMENTS = [
    {
        key: 'birth_certificate',
        title: 'Acta de Nacimiento',
        hint: 'PDF, JPG o PNG (Máx. 25MB)',
        accept: '.pdf,.jpg,.jpeg,.png,.webp,.heic,.heif',
        icon: 'fa-solid fa-file-lines',
    },
    {
        key: 'id_card',
        title: 'Cédula / Identidad',
        hint: 'PDF, JPG o PNG (Máx. 25MB)',
        accept: '.pdf,.jpg,.jpeg,.png,.webp,.heic,.heif',
        icon: 'fa-solid fa-id-card',
    },
    {
        key: 'high_school_record',
        title: 'Récord de Bachillerato',
        hint: 'PDF, JPG o PNG (Máx. 25MB)',
        accept: '.pdf,.jpg,.jpeg,.png,.webp,.heic,.heif',
        icon: 'fa-solid fa-scroll',
    },
    {
        key: 'photo',
        title: 'Fotografía 2x2',
        hint: 'JPG, PNG o PDF (Máx. 25MB)',
        accept: '.jpg,.jpeg,.png,.webp,.pdf,.heic,.heif',
        icon: 'fa-solid fa-image',
    },
];

export default function Portal({ applicant, admission, courses = [] }) {
    const { flash = {}, csrf_token: pageCsrfToken, errors: pageErrors = {} } = usePage().props;
    const [step, setStep] = useState(admission ? 'status' : 'form');
    const [uploadingKey, setUploadingKey] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadError, setUploadError] = useState(null);
    const [uploadSuccess, setUploadSuccess] = useState(null);
    const [currentAdmissionId, setCurrentAdmissionId] = useState(admission?.id || null);
    const [uploadedDocs, setUploadedDocs] = useState(admission?.documents || {});
    const [docStatuses, setDocStatuses] = useState(admission?.document_status || {});

    useEffect(() => {
        if (admission) {
            setCurrentAdmissionId(admission.id || null);
            setUploadedDocs(admission.documents || {});
            setDocStatuses(admission.document_status || {});
        }
    }, [admission]);

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
        birth_certificate: null,
        id_card: null,
        high_school_record: null,
        photo: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        setUploadError(null);
        post('/portal-aspirante', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setData((prev) => ({
                    ...prev,
                    birth_certificate: null,
                    id_card: null,
                    high_school_record: null,
                    photo: null,
                }));
                setStep('status');
            },
        });
    };

    const handleQuickUpload = (docKey, file, inputEl = null) => {
        if (!file) return;
        setUploadingKey(docKey);
        setUploadProgress(1);
        setUploadError(null);
        setUploadSuccess(null);

        const formData = new FormData();
        formData.append('key', docKey);
        formData.append('file', file, file.name);
        if (data.first_name) formData.append('first_name', data.first_name);
        if (data.last_name) formData.append('last_name', data.last_name);
        if (data.course_id) formData.append('course_id', String(data.course_id));

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/portal-aspirante/documento', true);

        const csrfHeaders = getCsrfHeaders(pageCsrfToken);
        Object.entries(csrfHeaders).forEach(([headerName, headerVal]) => {
            if (headerVal) {
                xhr.setRequestHeader(headerName, headerVal);
            }
        });
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = (event) => {
            if (event.lengthComputable && event.total > 0) {
                const pct = Math.max(1, Math.min(99, Math.round((event.loaded / event.total) * 100)));
                setUploadProgress(pct);
            }
        };

        xhr.onload = () => {
            let parsed = null;
            try {
                parsed = JSON.parse(xhr.responseText);
            } catch {
                parsed = null;
            }

            if (xhr.status >= 200 && xhr.status < 300 && parsed?.ok) {
                setUploadProgress(100);
                if (parsed.admission_id) {
                    setCurrentAdmissionId(parsed.admission_id);
                }
                if (parsed.documents) {
                    setUploadedDocs(parsed.documents);
                }
                if (parsed.document_status) {
                    setDocStatuses(parsed.document_status);
                }
                setUploadSuccess(parsed.message || 'Documento depositado correctamente.');
                router.reload({
                    only: ['admission', 'notifications', 'unreadNotificationsCount'],
                    preserveScroll: true,
                    preserveState: true,
                });
            } else {
                const firstValidationError =
                    parsed?.errors && Object.values(parsed.errors).flat()[0];
                setUploadError(
                    firstValidationError ||
                        parsed?.message ||
                        'No se pudo subir el archivo. Verifica que sea PDF, JPG o PNG (máx. 25 MB).'
                );
            }
        };

        xhr.onerror = () => {
            setUploadError('Error de red al subir el documento. Por favor intenta de nuevo.');
        };

        xhr.onloadend = () => {
            setUploadingKey(null);
            setUploadProgress(0);
            if (inputEl) {
                inputEl.value = '';
            }
        };

        xhr.send(formData);
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
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                                type="button"
                                onClick={() => setStep('status')}
                                className={`px-4 py-2 rounded-xl text-sm font-semibold transition-all ${
                                    step === 'status'
                                        ? 'bg-sga-primary text-white'
                                        : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="fa-solid fa-timeline mr-1.5"></i> Estatus
                            </button>
                            <button
                                type="button"
                                onClick={() => setStep('form')}
                                className={`px-4 py-2 rounded-xl text-sm font-semibold transition-all ${
                                    step === 'form'
                                        ? 'bg-sga-primary text-white'
                                        : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="fa-solid fa-pen-to-square mr-1.5"></i> Expediente
                            </button>
                        </div>
                    )}
                </div>

                {(uploadSuccess || flash?.success) && (
                    <div className="rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-xs font-semibold text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                        <i className="fa-solid fa-circle-check text-emerald-500"></i>
                        <span>{uploadSuccess || flash.success}</span>
                    </div>
                )}

                {(uploadError || Object.keys(errors || {}).length > 0 || Object.keys(pageErrors || {}).length > 0) && (
                    <div className="rounded-xl bg-rose-500/10 border border-rose-500/20 px-4 py-3 text-xs font-semibold text-rose-700 dark:text-rose-300 space-y-1">
                        {uploadError && (
                            <div className="flex items-center gap-2">
                                <i className="fa-solid fa-circle-exclamation text-rose-500"></i>
                                <span>{uploadError}</span>
                            </div>
                        )}
                        {Object.values({ ...(pageErrors || {}), ...(errors || {}) }).map((err, idx) => (
                            <div key={idx} className="flex items-center gap-2">
                                <i className="fa-solid fa-circle-exclamation text-rose-500"></i>
                                <span>{err}</span>
                            </div>
                        ))}
                    </div>
                )}

                {/* STATUS VIEW */}
                {step === 'status' && admission && (
                    <div className="space-y-6">
                        {/* Overall Status Card */}
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5">
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
                                        ADM-{String(currentAdmissionId || admission.id).padStart(5, '0')}
                                    </div>
                                </div>
                            </div>

                            {/* Documents Progress Checklist */}
                            <div className="pt-6">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                                    <h4 className="text-sm font-bold text-slate-800 dark:text-white">
                                        Documentación Requerida
                                    </h4>
                                    <button
                                        type="button"
                                        onClick={() => setStep('form')}
                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-sga-primary dark:text-blue-400 hover:underline"
                                    >
                                        <i className="fa-solid fa-cloud-arrow-up"></i>
                                        Depositar o actualizar documentos
                                    </button>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                    {REQUIRED_DOCUMENTS.map((doc) => {
                                        const isUploaded = Boolean(uploadedDocs?.[doc.key]);
                                        const rawStatus = docStatuses?.[doc.key] || 'pending';
                                        const isUploadingThis = uploadingKey === doc.key;
                                        const targetAdmissionId = currentAdmissionId || admission.id;

                                        const statusInfo = !isUploaded
                                            ? {
                                                  label: 'Sin depositar',
                                                  icon: 'fa-solid fa-cloud-arrow-up text-amber-500',
                                                  badge: 'text-amber-600 dark:text-amber-400',
                                              }
                                            : rawStatus === 'approved'
                                                ? {
                                                      label: 'Aprobado',
                                                      icon: 'fa-solid fa-circle-check text-emerald-500',
                                                      badge: 'text-emerald-600 dark:text-emerald-400',
                                                  }
                                                : rawStatus === 'rejected'
                                                    ? {
                                                          label: 'Observado',
                                                          icon: 'fa-solid fa-circle-exclamation text-rose-500',
                                                          badge: 'text-rose-600 dark:text-rose-400',
                                                      }
                                                    : {
                                                          label: 'Depositado',
                                                          icon: 'fa-solid fa-circle-check text-emerald-500',
                                                          badge: 'text-emerald-600 dark:text-emerald-400',
                                                      };

                                        return (
                                            <div key={doc.key} className="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/5 flex flex-col justify-between gap-2">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-3">
                                                        <i className={`${doc.icon} text-slate-400 text-lg`}></i>
                                                        <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">{doc.title}</span>
                                                    </div>
                                                    <i className={`${statusInfo.icon} text-sm`}></i>
                                                </div>
                                                {isUploadingThis && (
                                                    <div className="w-full h-1.5 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden">
                                                        <div
                                                            className="h-full bg-sga-primary transition-all duration-150"
                                                            style={{ width: `${uploadProgress || 10}%` }}
                                                        />
                                                    </div>
                                                )}
                                                <div className="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-white/5 text-[11px]">
                                                    <span className={`font-semibold ${statusInfo.badge}`}>
                                                        {isUploadingThis ? `Subiendo ${uploadProgress}%...` : statusInfo.label}
                                                    </span>
                                                    <div className="flex items-center gap-2">
                                                        {isUploaded && targetAdmissionId && (
                                                            <a
                                                                href={`/admissions/document/${targetAdmissionId}/${doc.key}`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="font-semibold text-sga-primary dark:text-blue-400 hover:underline"
                                                            >
                                                                Ver archivo
                                                            </a>
                                                        )}
                                                        <label className="cursor-pointer font-semibold text-sga-primary dark:text-blue-400 hover:underline">
                                                            {isUploadingThis ? (
                                                                <i className="fa-solid fa-circle-notch fa-spin"></i>
                                                            ) : isUploaded ? (
                                                                'Cambiar'
                                                            ) : (
                                                                'Subir ahora'
                                                            )}
                                                            <input
                                                                type="file"
                                                                accept={doc.accept}
                                                                disabled={isUploadingThis}
                                                                onChange={(e) => {
                                                                    const file = e.target.files?.[0];
                                                                    if (file) {
                                                                        handleQuickUpload(doc.key, file, e.target);
                                                                    }
                                                                }}
                                                                className="hidden"
                                                            />
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                                {admission.notes && (
                                    <div className="mt-4 rounded-xl bg-amber-500/10 border border-amber-500/20 p-4 text-xs text-amber-700 dark:text-amber-300">
                                        <span className="font-bold block mb-1">Observaciones de Admisiones:</span>
                                        {admission.notes}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Personal info summary */}
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 grid grid-cols-1 sm:grid-cols-3 gap-6">
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
                    <form onSubmit={handleSubmit} className="bg-white dark:bg-[#161622] rounded-2xl p-8 border border-slate-200 dark:border-white/5 space-y-6">
                        <div className="border-b border-slate-100 dark:border-white/5 pb-4">
                            <h3 className="text-xl font-bold text-slate-800 dark:text-white">
                                {admission ? 'Actualizar Información de Solicitud' : 'Formulario de Admisión e Ingreso'}
                            </h3>
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

                        {/* Depósito de Documentos Requeridos */}
                        <div className="pt-6 border-t border-slate-100 dark:border-white/5 space-y-4">
                            <div>
                                <h4 className="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                    <i className="fa-solid fa-cloud-arrow-up text-sga-primary dark:text-blue-400"></i>
                                    Depósito de Documentos de Admisión
                                </h4>
                                <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Adjunta cada documento requerido en formato PDF, JPG o PNG (máximo 25 MB por archivo). Se depositan automáticamente al seleccionarlos.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {REQUIRED_DOCUMENTS.map((doc) => {
                                    const selectedFile = data[doc.key];
                                    const alreadyUploaded = Boolean(uploadedDocs?.[doc.key]);
                                    const isUploadingThis = uploadingKey === doc.key;
                                    const targetAdmissionId = currentAdmissionId || admission?.id;
                                    const fieldError = errors?.[doc.key] || pageErrors?.[doc.key];

                                    return (
                                        <div
                                            key={doc.key}
                                            className="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 space-y-3"
                                        >
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="flex items-center gap-2.5">
                                                    <div className="w-9 h-9 rounded-lg bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 flex items-center justify-center text-sga-primary dark:text-blue-400">
                                                        <i className={doc.icon}></i>
                                                    </div>
                                                    <div>
                                                        <div className="text-xs font-bold text-slate-800 dark:text-white">
                                                            {doc.title}
                                                        </div>
                                                        <div className="text-[11px] text-slate-400">
                                                            {doc.hint}
                                                        </div>
                                                    </div>
                                                </div>

                                                {alreadyUploaded && targetAdmissionId && (
                                                    <a
                                                        href={`/admissions/document/${targetAdmissionId}/${doc.key}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline"
                                                    >
                                                        <i className="fa-solid fa-check"></i>
                                                        Depositado (Ver)
                                                    </a>
                                                )}
                                            </div>

                                            {isUploadingThis && (
                                                <div className="w-full h-1.5 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden">
                                                    <div
                                                        className="h-full bg-sga-primary transition-all duration-150"
                                                        style={{ width: `${uploadProgress || 10}%` }}
                                                    />
                                                </div>
                                            )}

                                            <label className="flex items-center justify-between gap-3 rounded-xl border border-dashed border-slate-300 dark:border-white/15 bg-white dark:bg-[#11111a] px-3.5 py-2.5 cursor-pointer hover:border-sga-primary dark:hover:border-blue-400 transition">
                                                <span className="text-xs text-slate-600 dark:text-slate-300 truncate">
                                                    {isUploadingThis
                                                        ? `Subiendo archivo (${uploadProgress}%)...`
                                                        : selectedFile
                                                            ? selectedFile.name
                                                            : alreadyUploaded
                                                                ? 'Archivo depositado • Clic para reemplazar...'
                                                                : 'Seleccionar archivo...'}
                                                </span>
                                                <span className="shrink-0 rounded-lg bg-slate-100 dark:bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                                    {isUploadingThis ? (
                                                        <i className="fa-solid fa-circle-notch fa-spin"></i>
                                                    ) : alreadyUploaded ? (
                                                        'Cambiar'
                                                    ) : (
                                                        'Examinar'
                                                    )}
                                                </span>
                                                <input
                                                    type="file"
                                                    accept={doc.accept}
                                                    disabled={isUploadingThis}
                                                    onChange={(e) => {
                                                        const file = e.target.files?.[0];
                                                        if (file) {
                                                            handleQuickUpload(doc.key, file, e.target);
                                                        }
                                                    }}
                                                    className="hidden"
                                                />
                                            </label>

                                            {fieldError && (
                                                <p className="text-xs font-medium text-rose-500">{fieldError}</p>
                                            )}
                                        </div>
                                    );
                                })}
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
                                className="px-8 py-3 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm transition-all flex items-center gap-2"
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
