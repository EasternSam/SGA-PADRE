import React, { useState } from 'react';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Login() {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.name || branding?.institution_name || 'Academic+';
    const primaryColor = branding?.primary_color || 'var(--brand-primary, #211170)';

    const [focusedInput, setFocusedInput] = useState('document_id');

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
        <KioskLayout showBack={false}>
            <Head title={`Kiosco - ${institutionName}`} />

            <div className="w-full max-w-2xl mx-auto flex flex-col justify-center">
                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight mb-6 sm:mb-8">
                    Ingresa tu identificación
                </h1>

                <div className="space-y-4">
                    {/* Cédula o Matrícula */}
                    <div>
                        <label className="block text-xs font-semibold text-neutral-500 mb-1.5">
                            Cédula o Matrícula
                        </label>
                        <div
                            onClick={() => setFocusedInput('document_id')}
                            style={focusedInput === 'document_id' ? { borderColor: primaryColor } : undefined}
                            className={`w-full px-4 py-3.5 rounded-xl bg-white dark:bg-[#141414] border-2 transition-all cursor-pointer flex items-center justify-between ${
                                focusedInput === 'document_id'
                                    ? ''
                                    : 'border-neutral-200 dark:border-[#262626]'
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
                        <label className="block text-xs font-semibold text-neutral-500 mb-1.5">
                            PIN
                        </label>
                        <div
                            onClick={() => setFocusedInput('pin')}
                            style={focusedInput === 'pin' ? { borderColor: primaryColor } : undefined}
                            className={`w-full px-4 py-3.5 rounded-xl bg-white dark:bg-[#141414] border-2 transition-all cursor-pointer flex items-center justify-between ${
                                focusedInput === 'pin'
                                    ? ''
                                    : 'border-neutral-200 dark:border-[#262626]'
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

                    {/* Continue Button (Image 4 style) */}
                    <div className="pt-2 flex items-center justify-between gap-4">
                        <div className="flex items-center gap-4 text-sm font-semibold opacity-75">
                            <Link href="/kiosk/hr-punch" className="hover:opacity-100">
                                Check-in Personal
                            </Link>
                            <Link href="/kiosk/academic-offer" className="hover:opacity-100">
                                Oferta Académica
                            </Link>
                        </div>

                        <button
                            type="button"
                            onClick={handleNextOrSubmit}
                            disabled={processing || !data.document_id}
                            style={
                                data.document_id && !processing
                                    ? { backgroundColor: primaryColor, color: '#FFFFFF' }
                                    : undefined
                            }
                            className={`min-w-[180px] px-6 py-4 rounded-xl font-semibold text-base flex items-center justify-between gap-4 transition-all active:scale-95 ${
                                data.document_id && !processing
                                    ? ''
                                    : 'bg-neutral-200 dark:bg-[#262626] text-neutral-400 cursor-not-allowed'
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
                </div>

                {/* Minimalist Bottom Keypad (Image 4) */}
                <div className="mt-6 p-3 sm:p-4 rounded-2xl bg-[#F3F4F6] dark:bg-[#171717]">
                    <div className="grid grid-cols-3 gap-2.5">
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                            <button
                                key={num}
                                type="button"
                                onClick={() => handleDigit(num.toString())}
                                className="h-13 sm:h-14 rounded-xl bg-white dark:bg-[#222222] text-xl sm:text-2xl font-semibold shadow-2xs transition-all active:scale-95 flex items-center justify-center"
                            >
                                {num}
                            </button>
                        ))}

                        <button
                            type="button"
                            onClick={handleClear}
                            className="h-13 sm:h-14 rounded-xl bg-neutral-200/80 dark:bg-[#262626] text-sm font-semibold transition-all active:scale-95 flex items-center justify-center"
                        >
                            C
                        </button>

                        <button
                            type="button"
                            onClick={() => handleDigit('0')}
                            className="h-13 sm:h-14 rounded-xl bg-white dark:bg-[#222222] text-xl sm:text-2xl font-semibold shadow-2xs transition-all active:scale-95 flex items-center justify-center"
                        >
                            0
                        </button>

                        <button
                            type="button"
                            onClick={handleDelete}
                            className="h-13 sm:h-14 rounded-xl bg-neutral-200/80 dark:bg-[#262626] transition-all active:scale-95 flex items-center justify-center"
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
