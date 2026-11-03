import React, { useState } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Login({ redirect = '' }) {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.name || branding?.institution_name || 'Academic+';
    const primaryColor = branding?.primary_color || '#211170';
    const secondaryColor = branding?.secondary_color || '#6b289e';

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

            <div className="w-full max-w-2xl mx-auto flex flex-col justify-center">
                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight mb-6 sm:mb-8">
                    Ingresa tu identificación
                </h1>

                <form onSubmit={handleNextOrSubmit} className="space-y-4">
                    {/* Cédula o Matrícula */}
                    <div>
                        <label className="block text-xs font-semibold opacity-70 mb-1.5">
                            Cédula o Matrícula
                        </label>
                        <div
                            onClick={() => setFocusedInput('document_id')}
                            style={focusedInput === 'document_id' ? { borderColor: primaryColor } : undefined}
                            className={`w-full px-4 py-3.5 rounded-xl kiosk-surface transition-all cursor-pointer flex items-center justify-between ${
                                focusedInput === 'document_id' ? 'ring-2 ring-[var(--kiosk-primary)]/20' : ''
                            }`}
                        >
                            <input
                                type="text"
                                value={data.document_id}
                                onFocus={() => setFocusedInput('document_id')}
                                onChange={(e) => setData('document_id', e.target.value)}
                                placeholder="260000001"
                                className="w-full bg-transparent text-xl sm:text-2xl font-semibold focus:outline-none font-mono"
                            />
                        </div>
                        {errors.document_id && (
                            <p className="text-xs text-rose-600 mt-1 font-medium">{errors.document_id}</p>
                        )}
                    </div>

                    {/* PIN */}
                    <div>
                        <label className="block text-xs font-semibold opacity-70 mb-1.5">
                            PIN
                        </label>
                        <div
                            onClick={() => setFocusedInput('pin')}
                            style={focusedInput === 'pin' ? { borderColor: primaryColor } : undefined}
                            className={`w-full px-4 py-3.5 rounded-xl kiosk-surface transition-all cursor-pointer flex items-center justify-between ${
                                focusedInput === 'pin' ? 'ring-2 ring-[var(--kiosk-primary)]/20' : ''
                            }`}
                        >
                            <input
                                type="password"
                                value={data.pin}
                                maxLength={6}
                                onFocus={() => setFocusedInput('pin')}
                                onChange={(e) => setData('pin', e.target.value)}
                                placeholder="••••"
                                className="w-full bg-transparent text-xl sm:text-2xl font-semibold focus:outline-none tracking-[0.35em] font-mono"
                            />
                        </div>
                        {errors.pin && (
                            <p className="text-xs text-rose-600 mt-1 font-medium">{errors.pin}</p>
                        )}
                    </div>

                    {/* Quick Access Links + Continue Button */}
                    <div className="pt-2 flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4 text-sm font-semibold">
                            <Link
                                href="/kiosk/hr-punch"
                                style={{ color: primaryColor }}
                                className="opacity-85 hover:opacity-100 transition-opacity"
                            >
                                Check-in Personal
                            </Link>
                            <Link
                                href="/kiosk/academic-offer"
                                style={{ color: secondaryColor }}
                                className="opacity-85 hover:opacity-100 transition-opacity"
                            >
                                Oferta Académica
                            </Link>
                            <Link
                                href="/kiosk/signup"
                                style={{ color: primaryColor }}
                                className="opacity-85 hover:opacity-100 transition-opacity"
                            >
                                Nuevo Ingreso
                            </Link>
                        </div>

                        <button
                            type="submit"
                            disabled={processing || !data.document_id}
                            style={
                                data.document_id && !processing
                                    ? {
                                          background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
                                          color: '#FFFFFF',
                                      }
                                    : undefined
                            }
                            className={`min-w-[180px] px-6 py-4 rounded-xl font-semibold text-base flex items-center justify-between gap-4 transition-all active:scale-95 ${
                                data.document_id && !processing
                                    ? 'shadow-sm'
                                    : 'kiosk-surface opacity-45 cursor-not-allowed'
                            }`}
                        >
                            <span>
                                {processing
                                    ? 'Verificando...'
                                    : focusedInput === 'document_id' && !data.pin
                                    ? 'Siguiente'
                                    : 'Continuar'}
                            </span>
                            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M5 12h14M13 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    </div>
                </form>

                {/* Minimalist Bottom Keypad adapted to Configuration Theme */}
                <div className="mt-6 p-3 sm:p-4 rounded-2xl kiosk-surface">
                    <div className="grid grid-cols-3 gap-2.5">
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                            <button
                                key={num}
                                type="button"
                                onClick={() => handleDigit(num.toString())}
                                className="h-13 sm:h-14 rounded-xl kiosk-key text-xl sm:text-2xl font-semibold shadow-2xs transition-all active:scale-95 flex items-center justify-center"
                            >
                                {num}
                            </button>
                        ))}

                        <button
                            type="button"
                            onClick={handleClear}
                            style={{ color: primaryColor }}
                            className="h-13 sm:h-14 rounded-xl kiosk-icon-btn text-sm font-bold transition-all active:scale-95 flex items-center justify-center"
                        >
                            C
                        </button>

                        <button
                            type="button"
                            onClick={() => handleDigit('0')}
                            className="h-13 sm:h-14 rounded-xl kiosk-key text-xl sm:text-2xl font-semibold shadow-2xs transition-all active:scale-95 flex items-center justify-center"
                        >
                            0
                        </button>

                        <button
                            type="button"
                            onClick={handleDelete}
                            style={{ color: primaryColor }}
                            className="h-13 sm:h-14 rounded-xl kiosk-icon-btn transition-all active:scale-95 flex items-center justify-center"
                        >
                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM18 9l-6 6M12 9l6 6" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </KioskLayout>
    );
}
