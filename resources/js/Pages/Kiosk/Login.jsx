import React, { useState } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Login() {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.institution_name || 'ACADEMIC+';

    // Wizard step: 'welcome' (Image 2 & 3 style) or 'student_auth' (Image 4 style)
    const [step, setStep] = useState('student_auth');
    const [focusedInput, setFocusedInput] = useState('document_id'); // 'document_id' | 'pin'

    const { data, setData, post, processing, errors } = useForm({
        document_id: '',
        pin: '',
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
        <KioskLayout title="" showBack={false}>
            <Head title={`Kiosco de Autoservicio - ${institutionName}`} />

            <div className="max-w-4xl mx-auto w-full py-2">
                {/* Top Quick Mode Switcher (Images 2, 3, 4) */}
                <div className="text-center mb-6">
                    <p className="text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Hola, bienvenido a <span className="text-[#166534] dark:text-emerald-400 font-bold">{institutionName}</span>
                    </p>
                    <h1 className="text-3xl sm:text-4xl font-bold text-[#111827] dark:text-white tracking-tight mt-1">
                        ¿Qué deseas realizar hoy?
                    </h1>
                </div>

                {/* Quick Portal Selector Tiles (Inspired by Image 2 & Image 3) */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 max-w-3xl mx-auto">
                    <button
                        type="button"
                        onClick={() => setStep('student_auth')}
                        className={`p-5 rounded-2xl border-2 text-left transition-all active:scale-[0.98] flex items-center gap-4 ${
                            step === 'student_auth'
                                ? 'bg-white dark:bg-slate-800 border-[#166534] shadow-md'
                                : 'bg-[#F3F4F6] dark:bg-slate-800/60 border-transparent hover:border-slate-300'
                        }`}
                    >
                        <div className="w-12 h-12 rounded-xl bg-[#166534] text-white flex items-center justify-center shrink-0">
                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <div className="font-bold text-base text-[#111827] dark:text-white leading-snug">
                                Soy Estudiante
                            </div>
                            <div className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Autopago, horario y notas
                            </div>
                        </div>
                    </button>

                    <Link
                        href="/kiosk/hr-punch"
                        className="p-5 rounded-2xl bg-[#F3F4F6] hover:bg-[#EAECEF] dark:bg-slate-800/80 dark:hover:bg-slate-800 border-2 border-transparent hover:border-[#166534]/40 transition-all active:scale-[0.98] flex items-center gap-4"
                    >
                        <div className="w-12 h-12 rounded-xl bg-[#1F2937] text-white flex items-center justify-center shrink-0">
                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <div className="font-bold text-base text-[#111827] dark:text-white leading-snug">
                                Check-in / Out
                            </div>
                            <div className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Reloj de personal y docentes
                            </div>
                        </div>
                    </Link>

                    <Link
                        href="/kiosk/academic-offer"
                        className="p-5 rounded-2xl bg-[#F3F4F6] hover:bg-[#EAECEF] dark:bg-slate-800/80 dark:hover:bg-slate-800 border-2 border-transparent hover:border-[#166534]/40 transition-all active:scale-[0.98] flex items-center gap-4"
                    >
                        <div className="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <div className="font-bold text-base text-[#111827] dark:text-white leading-snug">
                                Oferta Académica
                            </div>
                            <div className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Carreras, cursos y precios
                            </div>
                        </div>
                    </Link>
                </div>

                {/* Main Touch Input + Integrated Keypad (Directly inspired by Image 4 right screen) */}
                <div className="max-w-3xl mx-auto rounded-[28px] bg-[#F8FAFC] dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/80 p-6 md:p-8 shadow-xs">
                    <div className="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                        {/* Left Column: Clean Step-by-Step Inputs & Continue Button (Image 4) */}
                        <div className="md:col-span-6 space-y-5">
                            <div>
                                <h2 className="text-xl md:text-2xl font-bold text-[#111827] dark:text-white">
                                    Por favor, ingresa tu identificación
                                </h2>
                                <p className="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    Toca el campo que deseas completar con el teclado táctil.
                                </p>
                            </div>

                            {/* Field 1: Cédula o Matrícula */}
                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    Cédula o Matrícula
                                </label>
                                <div
                                    onClick={() => setFocusedInput('document_id')}
                                    className={`w-full px-4 py-3.5 rounded-xl bg-white dark:bg-slate-900 border-2 transition-all cursor-pointer flex items-center justify-between ${
                                        focusedInput === 'document_id'
                                            ? 'border-[#166534] ring-4 ring-[#166534]/10'
                                            : 'border-slate-300 dark:border-slate-700 hover:border-slate-400'
                                    }`}
                                >
                                    <input
                                        type="text"
                                        value={data.document_id}
                                        onFocus={() => setFocusedInput('document_id')}
                                        onChange={(e) => setData('document_id', e.target.value)}
                                        placeholder="Ej: 260000001 o 40212345678"
                                        className="w-full bg-transparent text-xl md:text-2xl font-bold text-[#111827] dark:text-white placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none font-mono"
                                    />
                                    {data.document_id && (
                                        <button
                                            type="button"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                setData('document_id', '');
                                            }}
                                            className="text-slate-400 hover:text-slate-600 text-lg px-1"
                                        >
                                            ×
                                        </button>
                                    )}
                                </div>
                                {errors.document_id && (
                                    <p className="text-xs text-rose-600 mt-1 font-medium">{errors.document_id}</p>
                                )}
                            </div>

                            {/* Field 2: PIN de Seguridad */}
                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    PIN de Seguridad (4 dígitos)
                                </label>
                                <div
                                    onClick={() => setFocusedInput('pin')}
                                    className={`w-full px-4 py-3.5 rounded-xl bg-white dark:bg-slate-900 border-2 transition-all cursor-pointer flex items-center justify-between ${
                                        focusedInput === 'pin'
                                            ? 'border-[#166534] ring-4 ring-[#166534]/10'
                                            : 'border-slate-300 dark:border-slate-700 hover:border-slate-400'
                                    }`}
                                >
                                    <input
                                        type="password"
                                        value={data.pin}
                                        maxLength={6}
                                        onFocus={() => setFocusedInput('pin')}
                                        onChange={(e) => setData('pin', e.target.value)}
                                        placeholder="••••"
                                        className="w-full bg-transparent text-xl md:text-2xl font-bold text-[#111827] dark:text-white placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none tracking-[0.35em] font-mono"
                                    />
                                    {data.pin && (
                                        <button
                                            type="button"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                setData('pin', '');
                                            }}
                                            className="text-slate-400 hover:text-slate-600 text-lg px-1"
                                        >
                                            ×
                                        </button>
                                    )}
                                </div>
                                {errors.pin && (
                                    <p className="text-xs text-rose-600 mt-1 font-medium">{errors.pin}</p>
                                )}
                            </div>

                            {/* Primary Action Button (Image 4 "Continue ->" green rectangular button) */}
                            <div className="pt-2 flex justify-end">
                                <button
                                    type="button"
                                    onClick={handleNextOrSubmit}
                                    disabled={processing || !data.document_id}
                                    className={`w-full sm:w-auto min-w-[200px] px-7 py-4 rounded-xl font-bold text-base flex items-center justify-between gap-4 transition-all active:scale-95 ${
                                        data.document_id && !processing
                                            ? 'bg-[#3B7A57] hover:bg-[#2E6346] text-white shadow-md'
                                            : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'
                                    }`}
                                >
                                    <span>
                                        {processing
                                            ? 'Verificando...'
                                            : focusedInput === 'document_id' && !data.pin
                                            ? 'Siguiente (PIN)'
                                            : 'Continuar'}
                                    </span>
                                    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.3">
                                        <path d="M5 12h14M13 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {/* Right Column: Clean Daylight Touch Keypad (Image 4 bottom keyboard aesthetic) */}
                        <div className="md:col-span-6">
                            <div className="p-4 rounded-2xl bg-[#E5E7EB] dark:bg-slate-900/90 shadow-inner">
                                <div className="flex items-center justify-between px-1 pb-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    <span>
                                        Editando:{' '}
                                        <strong className="text-[#111827] dark:text-white">
                                            {focusedInput === 'document_id' ? 'Cédula / Matrícula' : 'PIN de Seguridad'}
                                        </strong>
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setFocusedInput(focusedInput === 'document_id' ? 'pin' : 'document_id')
                                        }
                                        className="text-[#166534] dark:text-emerald-400 font-bold hover:underline"
                                    >
                                        Cambiar campo
                                    </button>
                                </div>

                                <div className="grid grid-cols-3 gap-2.5">
                                    {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                                        <button
                                            key={num}
                                            type="button"
                                            onClick={() => handleDigit(num.toString())}
                                            className="h-14 rounded-xl bg-white hover:bg-slate-50 active:bg-[#166534] active:text-white dark:bg-slate-800 dark:hover:bg-slate-700 text-2xl font-semibold text-[#111827] dark:text-white shadow-xs transition-all active:scale-95 flex items-center justify-center"
                                        >
                                            {num}
                                        </button>
                                    ))}

                                    <button
                                        type="button"
                                        onClick={handleClear}
                                        className="h-14 rounded-xl bg-slate-300/80 hover:bg-slate-300 dark:bg-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 transition-all active:scale-95 flex items-center justify-center"
                                    >
                                        Limpiar
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => handleDigit('0')}
                                        className="h-14 rounded-xl bg-white hover:bg-slate-50 active:bg-[#166534] active:text-white dark:bg-slate-800 dark:hover:bg-slate-700 text-2xl font-semibold text-[#111827] dark:text-white shadow-xs transition-all active:scale-95 flex items-center justify-center"
                                    >
                                        0
                                    </button>

                                    <button
                                        type="button"
                                        onClick={handleDelete}
                                        className="h-14 rounded-xl bg-slate-300/80 hover:bg-slate-300 dark:bg-slate-700 text-slate-800 dark:text-slate-200 transition-all active:scale-95 flex items-center justify-center"
                                        title="Borrar"
                                    >
                                        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM18 9l-6 6M12 9l6 6" strokeLinecap="round" strokeLinejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </KioskLayout>
    );
}
