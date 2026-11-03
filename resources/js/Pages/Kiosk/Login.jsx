import React, { useState, useEffect, useRef } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Login({ redirect = '' }) {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.name || branding?.institution_name || 'Academic+';
    const primaryColor = branding?.primary_color || '#181956';
    const secondaryColor = branding?.secondary_color || '#064e3b';
    const accentColor = branding?.accent_color || '#10b981';

    const [focusedInput, setFocusedInput] = useState('document_id');
    const [showPin, setShowPin] = useState(false);
    const docInputRef = useRef(null);
    const pinInputRef = useRef(null);

    const { data, setData, post, processing, errors } = useForm({
        document_id: '',
        pin: '',
        redirect: redirect || '',
    });

    const selectField = (field) => {
        setFocusedInput(field);
        if (field === 'document_id') {
            docInputRef.current?.focus();
        } else {
            pinInputRef.current?.focus();
        }
    };

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
            selectField('pin');
            return;
        }
        if (!data.document_id || !data.pin) return;
        post('/kiosk/login');
    };

    const isReadyToSubmit = Boolean(data.document_id && data.pin.length >= 4);

    return (
        <KioskLayout student={null} showBack={false}>
            <Head title={`Kiosco - ${institutionName}`} />

            <div className="w-full flex flex-col justify-between gap-6">
                {/* Main Split Grid: Left (Inputs & CTA) + Right (Ergonomic Touch Keypad) */}
                <form
                    onSubmit={handleNextOrSubmit}
                    className="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-center"
                >
                    {/* Left Column: Header + Clean Single-Border Inputs + Submit CTA */}
                    <div className="md:col-span-7 space-y-5">
                        <div>
                            <div
                                style={{ color: accentColor }}
                                className="text-xs font-extrabold uppercase tracking-widest mb-1"
                            >
                                Terminal de Autoservicio
                            </div>
                            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Iniciar Sesión
                            </h1>
                            <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Toca un campo y utiliza el teclado numérico para ingresar.
                            </p>
                        </div>

                        {/* Campo 1: Cédula o Matrícula (Sin doble borde) */}
                        <div>
                            <div className="flex items-center justify-between mb-1.5">
                                <label className="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Cédula o Matrícula
                                </label>
                                {focusedInput === 'document_id' && (
                                    <span
                                        style={{
                                            backgroundColor: `color-mix(in srgb, ${accentColor} 15%, transparent)`,
                                            color: accentColor,
                                        }}
                                        className="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                    >
                                        Teclado activo
                                    </span>
                                )}
                            </div>

                            <div
                                onClick={() => selectField('document_id')}
                                style={
                                    focusedInput === 'document_id'
                                        ? {
                                              borderColor: primaryColor,
                                              boxShadow: `0 0 0 3px color-mix(in srgb, ${primaryColor} 14%, transparent)`,
                                          }
                                        : undefined
                                }
                                className={`w-full px-4 py-3.5 rounded-2xl border-2 bg-slate-50/70 dark:bg-slate-800/70 transition-all cursor-pointer flex items-center gap-3.5 ${
                                    focusedInput === 'document_id'
                                        ? 'bg-white dark:bg-slate-800'
                                        : 'border-slate-200 dark:border-slate-700/80 hover:border-slate-300'
                                }`}
                            >
                                <div
                                    style={
                                        focusedInput === 'document_id'
                                            ? { backgroundColor: primaryColor, color: '#FFFFFF' }
                                            : undefined
                                    }
                                    className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors ${
                                        focusedInput === 'document_id'
                                            ? ''
                                            : 'bg-slate-200/70 dark:bg-slate-700 text-slate-500 dark:text-slate-300'
                                    }`}
                                >
                                    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <rect x="3" y="4" width="18" height="16" rx="3" />
                                        <circle cx="9" cy="10" r="2" />
                                        <path d="M15 8h2M15 12h2M7 16h10" strokeLinecap="round" />
                                    </svg>
                                </div>

                                <input
                                    ref={docInputRef}
                                    type="text"
                                    value={data.document_id}
                                    onFocus={() => setFocusedInput('document_id')}
                                    onChange={(e) => setData('document_id', e.target.value)}
                                    placeholder="Ej. 260000001"
                                    className="kiosk-bare-input w-full text-lg sm:text-xl font-bold text-slate-900 dark:text-white placeholder-slate-400 font-mono tracking-tight"
                                />

                                {data.document_id && (
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setData('document_id', '');
                                            selectField('document_id');
                                        }}
                                        className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1"
                                        aria-label="Limpiar matrícula"
                                    >
                                        <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                            <path d="M18 6L6 18M6 6l12 12" strokeLinecap="round" />
                                        </svg>
                                    </button>
                                )}
                            </div>
                            {errors.document_id && (
                                <p className="text-xs text-rose-600 mt-1.5 font-semibold">{errors.document_id}</p>
                            )}
                        </div>

                        {/* Campo 2: PIN de Acceso (Sin doble borde + Indicadores visuales) */}
                        <div>
                            <div className="flex items-center justify-between mb-1.5">
                                <label className="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">
                                    PIN de Seguridad
                                </label>
                                {focusedInput === 'pin' && (
                                    <span
                                        style={{
                                            backgroundColor: `color-mix(in srgb, ${accentColor} 15%, transparent)`,
                                            color: accentColor,
                                        }}
                                        className="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                    >
                                        Teclado activo
                                    </span>
                                )}
                            </div>

                            <div
                                onClick={() => selectField('pin')}
                                style={
                                    focusedInput === 'pin'
                                        ? {
                                              borderColor: primaryColor,
                                              boxShadow: `0 0 0 3px color-mix(in srgb, ${primaryColor} 14%, transparent)`,
                                          }
                                        : undefined
                                }
                                className={`w-full px-4 py-3.5 rounded-2xl border-2 bg-slate-50/70 dark:bg-slate-800/70 transition-all cursor-pointer flex items-center gap-3.5 ${
                                    focusedInput === 'pin'
                                        ? 'bg-white dark:bg-slate-800'
                                        : 'border-slate-200 dark:border-slate-700/80 hover:border-slate-300'
                                }`}
                            >
                                <div
                                    style={
                                        focusedInput === 'pin'
                                            ? { backgroundColor: primaryColor, color: '#FFFFFF' }
                                            : undefined
                                    }
                                    className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors ${
                                        focusedInput === 'pin'
                                            ? ''
                                            : 'bg-slate-200/70 dark:bg-slate-700 text-slate-500 dark:text-slate-300'
                                    }`}
                                >
                                    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <rect x="4" y="11" width="16" height="10" rx="2" />
                                        <path d="M8 11V7a4 4 0 0 1 8 0v4" strokeLinecap="round" />
                                    </svg>
                                </div>

                                <input
                                    ref={pinInputRef}
                                    type={showPin ? 'text' : 'password'}
                                    value={data.pin}
                                    maxLength={6}
                                    onFocus={() => setFocusedInput('pin')}
                                    onChange={(e) => setData('pin', e.target.value.replace(/[^0-9]/g, ''))}
                                    placeholder="••••"
                                    className="kiosk-bare-input w-full text-lg sm:text-xl font-bold text-slate-900 dark:text-white placeholder-slate-400 font-mono tracking-[0.4em]"
                                />

                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        setShowPin((prev) => !prev);
                                    }}
                                    className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1"
                                    aria-label="Mostrar PIN"
                                >
                                    {showPin ? (
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18" />
                                        </svg>
                                    ) : (
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    )}
                                </button>
                            </div>
                            {errors.pin && (
                                <p className="text-xs text-rose-600 mt-1.5 font-semibold">{errors.pin}</p>
                            )}
                        </div>

                        {/* Botón Principal de Acción con Colores Institucionales Sólidos */}
                        <div className="pt-1">
                            <button
                                type="submit"
                                disabled={processing}
                                style={{
                                    background: isReadyToSubmit
                                        ? `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
                                        : primaryColor,
                                }}
                                className="w-full py-4 px-6 text-white font-bold text-base tracking-wide transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center gap-3 hover:brightness-110 rounded-2xl shadow-lg shadow-slate-900/15"
                            >
                                <span>
                                    {processing
                                        ? 'Verificando credenciales...'
                                        : focusedInput === 'document_id' && data.document_id && !data.pin
                                        ? 'Continuar al PIN'
                                        : 'Ingresar al Kiosco'}
                                </span>
                                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4">
                                    <path d="M5 12h14M13 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {/* Right Column: Ergonomic Tactile Numeric Pad */}
                    <div className="md:col-span-5">
                        <div className="p-4 rounded-3xl kiosk-surface max-w-[290px] mx-auto shadow-inner">
                            <div className="grid grid-cols-3 gap-2.5">
                                {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                                    <button
                                        key={num}
                                        type="button"
                                        onClick={() => handleDigit(num.toString())}
                                        className="h-14 sm:h-15 rounded-2xl kiosk-key text-xl sm:text-2xl font-bold shadow-xs hover:shadow-md transition-all active:scale-95 flex items-center justify-center"
                                    >
                                        {num}
                                    </button>
                                ))}

                                <button
                                    type="button"
                                    onClick={handleClear}
                                    style={{ color: primaryColor }}
                                    className="h-14 sm:h-15 rounded-2xl kiosk-icon-btn text-xs font-extrabold uppercase tracking-wider transition-all active:scale-95 flex items-center justify-center"
                                >
                                    Borrar
                                </button>

                                <button
                                    type="button"
                                    onClick={() => handleDigit('0')}
                                    className="h-14 sm:h-15 rounded-2xl kiosk-key text-xl sm:text-2xl font-bold shadow-xs hover:shadow-md transition-all active:scale-95 flex items-center justify-center"
                                >
                                    0
                                </button>

                                <button
                                    type="button"
                                    onClick={handleDelete}
                                    style={{ color: primaryColor }}
                                    className="h-14 sm:h-15 rounded-2xl kiosk-icon-btn transition-all active:scale-95 flex items-center justify-center"
                                    aria-label="Retroceder"
                                >
                                    <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                        <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM18 9l-6 6M12 9l6 6" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {/* Bottom Quick-Access Cards (Replaces floating links with clean tactile cards) */}
                <div className="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <Link
                        href="/kiosk/hr-punch"
                        className="group p-3.5 rounded-2xl kiosk-card-interactive flex items-center gap-3 active:scale-[0.98]"
                    >
                        <div
                            style={{ backgroundColor: primaryColor }}
                            className="w-9 h-9 rounded-xl text-white flex items-center justify-center shrink-0"
                        >
                            <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <div className="text-left min-w-0">
                            <div className="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">
                                Check-in Personal
                            </div>
                            <div className="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                Ponche de asistencia
                            </div>
                        </div>
                    </Link>

                    <Link
                        href="/kiosk/academic-offer"
                        className="group p-3.5 rounded-2xl kiosk-card-interactive flex items-center gap-3 active:scale-[0.98]"
                    >
                        <div
                            style={{ backgroundColor: secondaryColor }}
                            className="w-9 h-9 rounded-xl text-white flex items-center justify-center shrink-0"
                        >
                            <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M12 3L2 8l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <div className="text-left min-w-0">
                            <div className="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">
                                Oferta Académica
                            </div>
                            <div className="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                Carreras y programas
                            </div>
                        </div>
                    </Link>

                    <Link
                        href="/kiosk/signup"
                        className="group p-3.5 rounded-2xl kiosk-card-interactive flex items-center gap-3 active:scale-[0.98]"
                    >
                        <div
                            style={{ backgroundColor: accentColor }}
                            className="w-9 h-9 rounded-xl text-white flex items-center justify-center shrink-0"
                        >
                            <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" strokeLinecap="round" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M19 8v6M22 11h-6" strokeLinecap="round" />
                            </svg>
                        </div>
                        <div className="text-left min-w-0">
                            <div className="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">
                                Solicitar Admisión
                            </div>
                            <div className="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                Nuevo ingreso
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </KioskLayout>
    );
}
