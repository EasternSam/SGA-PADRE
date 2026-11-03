import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function HrPunch({ recentPunches = [] }) {
    const [punchType, setPunchType] = useState(0); // 0 = Check-in (Entrada), 1 = Check-out (Salida)
    const [pin, setPin] = useState('');
    const [processing, setProcessing] = useState(false);
    const [currentTime, setCurrentTime] = useState('');
    const [currentDate, setCurrentDate] = useState('');

    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            setCurrentTime(
                now.toLocaleTimeString('es-DO', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                })
            );
            setCurrentDate(
                now.toLocaleDateString('es-DO', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                })
            );
        };
        updateTime();
        const interval = setInterval(updateTime, 1000);
        return () => clearInterval(interval);
    }, []);

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
        <KioskLayout title="Check-in / Check-out de Personal" showBack={true} backUrl="/kiosk/login">
            <Head title="Check-in / Check-out - Kiosco" />

            <div className="max-w-5xl mx-auto w-full py-2">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                    {/* Left Column: Bachmann Group Check-in / Check-out Hero Card (Reference Image 3) */}
                    <div className="lg:col-span-6 flex flex-col justify-between rounded-[28px] bg-gradient-to-br from-[#1B3B2B] via-[#234936] to-[#11261B] text-white p-7 md:p-9 shadow-lg relative overflow-hidden">
                        {/* Subtle organic light pattern */}
                        <div className="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none" />

                        <div className="relative z-10">
                            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-emerald-200 text-xs font-mono font-bold mb-5">
                                <span>{currentTime || '08:00:00'}</span>
                                <span>•</span>
                                <span className="capitalize">{currentDate}</span>
                            </div>

                            <h1 className="text-4xl md:text-5xl font-bold tracking-tight leading-tight">
                                Bienvenido,
                            </h1>
                            <p className="text-sm md:text-base text-emerald-100/85 mt-2 max-w-md leading-relaxed">
                                ¿Deseas registrar tu entrada (Check-in) o tu salida (Check-out)?
                                Selecciona tu opción abajo.
                            </p>

                            {/* Large Check-in & Check-out Blocks (Exact Match to Reference Image 3) */}
                            <div className="mt-7 space-y-4">
                                {/* Check-in Block */}
                                <button
                                    type="button"
                                    onClick={() => setPunchType(0)}
                                    className={`w-full p-5 rounded-2xl bg-white text-[#111827] flex items-center justify-between transition-all active:scale-[0.98] border-4 ${
                                        punchType === 0
                                            ? 'border-[#4ADE80] shadow-xl scale-[1.01]'
                                            : 'border-transparent opacity-80 hover:opacity-100'
                                    }`}
                                >
                                    <div className="flex items-center gap-5">
                                        <div className="w-14 h-14 rounded-xl bg-[#3B7A57] text-white flex items-center justify-center shrink-0">
                                            <svg className="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" strokeLinecap="round" strokeLinejoin="round" />
                                            </svg>
                                        </div>
                                        <div className="text-left">
                                            <div className="text-2xl font-bold tracking-tight text-[#111827]">
                                                Check-in
                                            </div>
                                            <div className="text-xs font-medium text-slate-500">
                                                Registrar Entrada de Jornada
                                            </div>
                                        </div>
                                    </div>
                                    {punchType === 0 && (
                                        <span className="px-3 py-1 rounded-full bg-emerald-100 text-[#166534] text-xs font-bold">
                                            Seleccionado
                                        </span>
                                    )}
                                </button>

                                {/* Check-out Block */}
                                <button
                                    type="button"
                                    onClick={() => setPunchType(1)}
                                    className={`w-full p-5 rounded-2xl bg-white text-[#111827] flex items-center justify-between transition-all active:scale-[0.98] border-4 ${
                                        punchType === 1
                                            ? 'border-[#4ADE80] shadow-xl scale-[1.01]'
                                            : 'border-transparent opacity-80 hover:opacity-100'
                                    }`}
                                >
                                    <div className="flex items-center gap-5">
                                        <div className="w-14 h-14 rounded-xl bg-[#1F2937] text-white flex items-center justify-center shrink-0">
                                            <svg className="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" strokeLinecap="round" strokeLinejoin="round" />
                                            </svg>
                                        </div>
                                        <div className="text-left">
                                            <div className="text-2xl font-bold tracking-tight text-[#111827]">
                                                Check-out
                                            </div>
                                            <div className="text-xs font-medium text-slate-500">
                                                Registrar Salida de Jornada
                                            </div>
                                        </div>
                                    </div>
                                    {punchType === 1 && (
                                        <span className="px-3 py-1 rounded-full bg-slate-200 text-slate-800 text-xs font-bold">
                                            Seleccionado
                                        </span>
                                    )}
                                </button>
                            </div>
                        </div>

                        {/* Recent Punches Compact Feed */}
                        <div className="relative z-10 mt-7 pt-5 border-t border-white/15">
                            <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-emerald-200/80 mb-2.5">
                                <span>Registros Recientes Hoy</span>
                                <span>{recentPunches.length} marcaje(s)</span>
                            </div>
                            {recentPunches.length === 0 ? (
                                <p className="text-xs text-emerald-100/60">
                                    Aún no se han registrado marcajes en el día de hoy.
                                </p>
                            ) : (
                                <div className="space-y-1.5 max-h-28 overflow-y-auto pr-1">
                                    {recentPunches.slice(0, 4).map((p) => (
                                        <div
                                            key={p.id}
                                            className="px-3 py-2 rounded-xl bg-white/10 flex items-center justify-between text-xs"
                                        >
                                            <span className="font-semibold truncate">{p.employee_name}</span>
                                            <div className="flex items-center gap-2 shrink-0 font-mono">
                                                <span className="opacity-80">{p.time}</span>
                                                <span
                                                    className={`px-2 py-0.5 rounded font-bold text-[10px] ${
                                                        p.type === 'Entrada'
                                                            ? 'bg-emerald-400/25 text-emerald-200'
                                                            : 'bg-white/20 text-white'
                                                    }`}
                                                >
                                                    {p.type}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right Column: Step Input + Continue Button + Touch Keypad (Reference Image 4 right screen) */}
                    <div className="lg:col-span-6 rounded-[28px] bg-[#F8FAFC] dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 p-7 md:p-9 flex flex-col justify-between shadow-xs">
                        <div>
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#E6F2F0] dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-300 text-xs font-bold mb-3">
                                <span>Modo activo: {punchType === 0 ? 'Check-in (Entrada)' : 'Check-out (Salida)'}</span>
                            </div>

                            <h2 className="text-2xl font-bold text-[#111827] dark:text-white">
                                Por favor, ingresa tu PIN o ID de empleado
                            </h2>

                            <div className="mt-4">
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    PIN / Código Biométrico / Cédula
                                </label>
                                <div className="w-full px-5 py-4 rounded-xl bg-white dark:bg-slate-900 border-2 border-[#3B7A57] flex items-center justify-between shadow-xs">
                                    <input
                                        type="password"
                                        value={pin}
                                        onChange={(e) => setPin(e.target.value)}
                                        placeholder="Introduce tu código..."
                                        className="w-full bg-transparent text-2xl font-mono font-bold text-[#111827] dark:text-white placeholder-slate-300 focus:outline-none tracking-widest"
                                    />
                                    {pin && (
                                        <button
                                            type="button"
                                            onClick={handleClear}
                                            className="text-slate-400 hover:text-slate-600 text-xl font-bold px-2"
                                        >
                                            ×
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Continue -> Button (Exact match to Image 4 right screen) */}
                            <div className="mt-5 flex justify-end">
                                <button
                                    type="button"
                                    disabled={!pin || processing}
                                    onClick={handleSubmit}
                                    className={`px-7 py-4 rounded-xl font-bold text-base flex items-center justify-between gap-5 min-w-[210px] transition-all active:scale-95 ${
                                        pin && !processing
                                            ? 'bg-[#3B7A57] hover:bg-[#2E6346] text-white shadow-md'
                                            : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'
                                    }`}
                                >
                                    <span>
                                        {processing
                                            ? 'Registrando...'
                                            : punchType === 0
                                            ? 'Confirmar Check-in'
                                            : 'Confirmar Check-out'}
                                    </span>
                                    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.3">
                                        <path d="M5 12h14M13 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {/* Integrated Bottom Keypad (Image 4 style) */}
                        <div className="mt-6 p-4 rounded-2xl bg-[#E5E7EB] dark:bg-slate-900 shadow-inner">
                            <div className="grid grid-cols-3 gap-2.5">
                                {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                                    <button
                                        key={num}
                                        type="button"
                                        onClick={() => handleDigit(num.toString())}
                                        className="h-14 rounded-xl bg-white hover:bg-slate-50 active:bg-[#3B7A57] active:text-white dark:bg-slate-800 text-2xl font-semibold text-[#111827] dark:text-white shadow-xs transition-all active:scale-95 flex items-center justify-center"
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
                                    className="h-14 rounded-xl bg-white hover:bg-slate-50 active:bg-[#3B7A57] active:text-white dark:bg-slate-800 text-2xl font-semibold text-[#111827] dark:text-white shadow-xs transition-all active:scale-95 flex items-center justify-center"
                                >
                                    0
                                </button>
                                <button
                                    type="button"
                                    onClick={handleDelete}
                                    className="h-14 rounded-xl bg-slate-300/80 hover:bg-slate-300 dark:bg-slate-700 text-slate-800 dark:text-slate-200 transition-all active:scale-95 flex items-center justify-center"
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
        </KioskLayout>
    );
}
