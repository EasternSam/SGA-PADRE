import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function HrPunch() {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';

    const [punchType, setPunchType] = useState(0); // 0 = Check-in, 1 = Check-out
    const [pin, setPin] = useState('');
    const [processing, setProcessing] = useState(false);

    const handleDigit = (digit) => {
        if (pin.length < 15) {
            setPin((prev) => prev + digit);
        }
    };

    const handleDelete = () => {
        setPin((prev) => prev.slice(0, -1));
    };

    const handleClear = () => {
        setPin('');
    };

    const handleSubmit = (e) => {
        e?.preventDefault();
        if (!pin || processing) return;

        setProcessing(true);
        router.post(
            '/kiosk/hr-punch',
            {
                pin,
                punch_type: punchType,
            },
            {
                onSuccess: () => setPin(''),
                onFinish: () => setProcessing(false),
            }
        );
    };

    return (
        <KioskLayout showBack={true} backUrl="/kiosk/login">
            <Head title="Check-in / Check-out - Kiosco" />

            <div className="w-full max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-7 items-center">
                {/* Left: Check-in / Check-out Touch Blocks (Image 3) */}
                <div className="lg:col-span-6 space-y-4">
                    <h1 className="text-3xl sm:text-4xl font-bold tracking-tight mb-4">
                        Bienvenido
                    </h1>

                    <button
                        type="button"
                        onClick={() => setPunchType(0)}
                        style={punchType === 0 ? { borderColor: primaryColor } : undefined}
                        className={`w-full p-6 rounded-2xl bg-[#F3F4F6] dark:bg-[#171717] flex items-center gap-5 transition-all active:scale-[0.98] border-2 ${
                            punchType === 0 ? '' : 'border-transparent opacity-70'
                        }`}
                    >
                        <div
                            style={{ backgroundColor: primaryColor }}
                            className="w-13 h-13 rounded-xl text-white flex items-center justify-center shrink-0"
                        >
                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <span className="text-2xl font-bold tracking-tight">Check-in</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setPunchType(1)}
                        style={punchType === 1 ? { borderColor: primaryColor } : undefined}
                        className={`w-full p-6 rounded-2xl bg-[#F3F4F6] dark:bg-[#171717] flex items-center gap-5 transition-all active:scale-[0.98] border-2 ${
                            punchType === 1 ? '' : 'border-transparent opacity-70'
                        }`}
                    >
                        <div className="w-13 h-13 rounded-xl bg-[#1F2937] text-white flex items-center justify-center shrink-0">
                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </div>
                        <span className="text-2xl font-bold tracking-tight">Check-out</span>
                    </button>
                </div>

                {/* Right: Input + Keypad (Image 4) */}
                <div className="lg:col-span-6 space-y-5">
                    <div>
                        <label className="block text-xs font-semibold text-neutral-500 mb-1.5">
                            PIN o Identificación
                        </label>
                        <div
                            style={{ borderColor: primaryColor }}
                            className="w-full px-5 py-4 rounded-xl bg-white dark:bg-[#141414] border-2 flex items-center justify-between"
                        >
                            <input
                                type="password"
                                value={pin}
                                onChange={(e) => setPin(e.target.value)}
                                placeholder="••••"
                                className="w-full bg-transparent text-2xl font-mono font-bold focus:outline-none tracking-widest"
                            />
                        </div>
                    </div>

                    <div className="flex justify-end">
                        <button
                            type="button"
                            disabled={!pin || processing}
                            onClick={handleSubmit}
                            style={
                                pin && !processing
                                    ? { backgroundColor: primaryColor, color: '#FFFFFF' }
                                    : undefined
                            }
                            className={`min-w-[190px] px-6 py-4 rounded-xl font-semibold text-base flex items-center justify-between gap-4 transition-all active:scale-95 ${
                                pin && !processing
                                    ? ''
                                    : 'bg-neutral-200 dark:bg-[#262626] text-neutral-400 cursor-not-allowed'
                            }`}
                        >
                            <span>{processing ? 'Registrando...' : 'Continuar'}</span>
                            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M5 12h14M13 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    </div>

                    <div className="p-3 sm:p-4 rounded-2xl bg-[#F3F4F6] dark:bg-[#171717]">
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
            </div>
        </KioskLayout>
    );
}
