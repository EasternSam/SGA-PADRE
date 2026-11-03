import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Signup({ student, courses = [], selectedCourseId = null }) {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';
    const secondaryColor = branding?.secondary_color || '#6b289e';

    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        cedula: '',
        mobile_phone: '',
        email: '',
        course_id: selectedCourseId || courses[0]?.id || '',
        pin: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/kiosk/signup');
    };

    return (
        <KioskLayout
            student={student}
            backUrl={student ? '/kiosk/dashboard' : '/kiosk/login'}
            showBack={true}
        >
            <Head title="Nuevo Ingreso - Kiosco" />

            <div className="w-full max-w-2xl mx-auto">
                <h1 className="text-2xl sm:text-3xl font-bold tracking-tight mb-6">
                    Registro de Nuevo Ingreso
                </h1>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold opacity-70 mb-1.5">
                                Nombres *
                            </label>
                            <input
                                type="text"
                                required
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                placeholder="Ej: Juan Carlos"
                                className="w-full px-4 py-3.5 rounded-xl kiosk-surface text-base sm:text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--kiosk-primary)]"
                            />
                            {errors.first_name && (
                                <p className="text-xs text-rose-600 mt-1">{errors.first_name}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold opacity-70 mb-1.5">
                                Apellidos *
                            </label>
                            <input
                                type="text"
                                required
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                placeholder="Ej: Pérez Gómez"
                                className="w-full px-4 py-3.5 rounded-xl kiosk-surface text-base sm:text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--kiosk-primary)]"
                            />
                            {errors.last_name && (
                                <p className="text-xs text-rose-600 mt-1">{errors.last_name}</p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold opacity-70 mb-1.5">
                                Cédula o Documento *
                            </label>
                            <input
                                type="text"
                                required
                                value={data.cedula}
                                onChange={(e) => setData('cedula', e.target.value)}
                                placeholder="001-0000000-0"
                                className="w-full px-4 py-3.5 rounded-xl kiosk-surface text-base sm:text-lg font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--kiosk-primary)]"
                            />
                            {errors.cedula && (
                                <p className="text-xs text-rose-600 mt-1">{errors.cedula}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold opacity-70 mb-1.5">
                                Teléfono Celular
                            </label>
                            <input
                                type="tel"
                                value={data.mobile_phone}
                                onChange={(e) => setData('mobile_phone', e.target.value)}
                                placeholder="809-000-0000"
                                className="w-full px-4 py-3.5 rounded-xl kiosk-surface text-base sm:text-lg font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--kiosk-primary)]"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold opacity-70 mb-1.5">
                                Programa Académico *
                            </label>
                            <select
                                required
                                value={data.course_id}
                                onChange={(e) => setData('course_id', e.target.value)}
                                className="w-full px-4 py-3.5 rounded-xl kiosk-surface text-base font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--kiosk-primary)]"
                            >
                                <option value="">Selecciona un programa...</option>
                                {courses.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                            {errors.course_id && (
                                <p className="text-xs text-rose-600 mt-1">{errors.course_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold opacity-70 mb-1.5">
                                PIN de Acceso Kiosco (4 dígitos) *
                            </label>
                            <input
                                type="password"
                                required
                                minLength={4}
                                maxLength={6}
                                value={data.pin}
                                onChange={(e) => setData('pin', e.target.value.replace(/[^0-9]/g, ''))}
                                placeholder="••••"
                                className="w-full px-4 py-3.5 rounded-xl kiosk-surface text-xl font-mono font-bold tracking-[0.35em] focus:outline-none focus:ring-2 focus:ring-[var(--kiosk-primary)]"
                            />
                            {errors.pin && (
                                <p className="text-xs text-rose-600 mt-1">{errors.pin}</p>
                            )}
                        </div>
                    </div>

                    <div className="pt-4 flex justify-end">
                        <button
                            type="submit"
                            disabled={processing || !data.first_name || !data.last_name || !data.cedula || !data.course_id || !data.pin}
                            style={{
                                background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
                                color: '#FFFFFF',
                            }}
                            className="px-8 py-4 rounded-xl font-semibold text-base flex items-center gap-3 transition-all active:scale-95 disabled:opacity-45"
                        >
                            <span>{processing ? 'Registrando...' : 'Completar Inscripción'}</span>
                            <span>→</span>
                        </button>
                    </div>
                </form>
            </div>
        </KioskLayout>
    );
}
