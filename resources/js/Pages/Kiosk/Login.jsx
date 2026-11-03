import React, { useState } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Login({ redirect = '' }) {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.name || branding?.institution_name || 'Academic+';
    const primaryColor = branding?.primary_color || '#181956';
    const accentColor = branding?.accent_color || '#10b981';

    const [focusedInput, setFocusedInput] = useState('document_id');

    const { data, setData, post, processing, errors } = useForm({
        document_id: '',
        pin: '',
        redirect: redirect || '',
    });

    const handleDigit = (digit) => {
        if (focusedInput === 'document_id') {
            if (data.document_id.length < 15) {
                setData('document_id', data.document_id + digit);
            }
        } else {
            if (data.pin.length < 6) {
                setData('pin', data.pin + digit);
            }
        }
    };

    const handleDelete = () => {
        if (focusedInput === 'document_id') {
            setData('document_id', data.document_id.slice(0, -1));
        } else {
            setData('pin', data.pin.slice(0, -1));
        }
    };

    const handleClear = () => {
        if (focusedInput === 'document_id') {
            setData('document_id', '');
        } else {
            setData('pin', '');
        }
    };

    const handleNextOrSubmit = (e) => {
        e?.preventDefault();
        if (focusedInput === 'document_id' && data.document_id && !data.pin) {
            setFocusedInput('pin');
            return;
        }
        if (!data.document_id || !data.pin) return;
        post('/kiosk/login');
    };

    return (
        <KioskLayout student={null} showBack={false}>
            <Head title={`Kiosco - ${institutionName}`} />

            <div className="w-full max-w-md mx-auto flex flex-col justify-center">
                <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-5">
                    Iniciar Sesión
                </h1>

                <form onSubmit={handleNextOrSubmit} className="space-y-4">
                    {/* Cédula o Matrícula */}
                    <div>
                        <label className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Cédula o Matrícula
                        </label>
                        <div
                            onClick={() => setFocusedInput('document_id')}
                            style={focusedInput === 'document_id' ? { borderColor: primaryColor } : undefined}
                            className={`w-full px-4 py-3 rounded-xl border bg-white dark:bg-slate-800/80 transition-all cursor-pointer flex items-center justify-between shadow-2xs ${
                                focusedInput === 'document_id'
                                    ? 'ring-2 ring-[var(--kiosk-primary)]/25'
                                    : 'border-slate-200 dark:border-slate-700'
                            }`}
                        >
                            <input
                                type="text"
                                value={data.document_id}
                                onFocus={() => setFocusedInput('document_id')}
                                onChange={(e) => setData('document_id', e.target.value)}
                                placeholder="260000001"
                                className="w-full bg-transparent text-lg sm:text-xl font-semibold text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none font-mono"
                            />
                        </div>
                        {errors.document_id && (
                            <p className="text-xs text-rose-600 mt-1 font-medium">{errors.document_id}</p>
                        )}
                    </div>

                    {/* PIN */}
                    <div>
                        <label className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            PIN de Acceso
                        </label>
                        <div
                            onClick={() => setFocusedInput('pin')}
                            style={focusedInput === 'pin' ? { borderColor: primaryColor } : undefined}
                            className={`w-full px-4 py-3 rounded-xl border bg-white dark:bg-slate-800/80 transition-all cursor-pointer flex items-center justify-between shadow-2xs ${
                                focusedInput === 'pin'
                                    ? 'ring-2 ring-[var(--kiosk-primary)]/25'
                                    : 'border-slate-200 dark:border-slate-700'
                            }`}
                        >
                            <input
                                type="password"
                                value={data.pin}
                                maxLength={6}
                                onFocus={() => setFocusedInput('pin')}
                                onChange={(e) => setData('pin', e.target.value)}
                                placeholder="••••"
                                className="w-full bg-transparent text-lg sm:text-xl font-semibold text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none tracking-[0.35em] font-mono"
                            />
                        </div>
                        {errors.pin && (
                            <p className="text-xs text-rose-600 mt-1 font-medium">{errors.pin}</p>
                        )}
                    </div>

                    {/* Keypad Táctil */}
                    <div className="p-3 rounded-2xl kiosk-surface">
                        <div className="grid grid-cols-3 gap-2">
                            {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                                <button
                                    key={num}
                                    type="button"
                                    onClick={() => handleDigit(num.toString())}
                                    className="h-12 sm:h-13 rounded-xl kiosk-key text-lg sm:text-xl font-bold shadow-2xs transition-all active:scale-95 flex items-center justify-center"
                                >
                                    {num}
                                </button>
                            ))}

                            <button
                                type="button"
                                onClick={handleClear}
                                style={{ color: primaryColor }}
                                className="h-12 sm:h-13 rounded-xl kiosk-icon-btn text-sm font-bold transition-all active:scale-95 flex items-center justify-center"
                            >
                                C
                            </button>

                            <button
                                type="button"
                                onClick={() => handleDigit('0')}
                                className="h-12 sm:h-13 rounded-xl kiosk-key text-lg sm:text-xl font-bold shadow-2xs transition-all active:scale-95 flex items-center justify-center"
                            >
                                0
                            </button>

                            <button
                                type="button"
                                onClick={handleDelete}
                                style={{ color: primaryColor }}
                                className="h-12 sm:h-13 rounded-xl kiosk-icon-btn transition-all active:scale-95 flex items-center justify-center"
                            >
                                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM18 9l-6 6M12 9l6 6" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {/* Botón Principal (Idéntico al botón de /login) */}
                    <div className="pt-1">
                        <button
                            type="submit"
                            disabled={processing || !data.document_id}
                            style={{ background: primaryColor }}
                            className="w-full py-4 px-4 text-white font-bold text-sm tracking-wide transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2 hover:brightness-110 rounded-xl disabled:opacity-50"
                        >
                            <span>
                                {processing
                                    ? 'Verificando...'
                                    : focusedInput === 'document_id' && !data.pin
                                    ? 'Siguiente'
                                    : 'Ingresar al Kiosco'}
                            </span>
                            <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                                <path d="M9 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    </div>

                    {/* Enlaces Rápidos inferiores */}
                    <div className="pt-2 flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm font-semibold">
                        <Link
                            href="/kiosk/hr-punch"
                            style={{ color: primaryColor }}
                            className="dark:!text-emerald-400 hover:underline"
                        >
                            Check-in Personal
                        </Link>
                        <Link
                            href="/kiosk/academic-offer"
                            style={{ color: accentColor }}
                            className="hover:underline"
                        >
                            Oferta Académica
                        </Link>
                        <Link
                            href="/kiosk/signup"
                            style={{ color: primaryColor }}
                            className="dark:!text-emerald-400 hover:underline"
                        >
                            Solicitar Admisión
                        </Link>
                    </div>
                </form>
            </div>
        </KioskLayout>
    );
}
