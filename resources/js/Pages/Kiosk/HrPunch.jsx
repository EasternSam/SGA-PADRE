import React, { useState, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function HrPunch({ recentPunches = [] }) {
    const [punchType, setPunchType] = useState(0); // 0 = Entrada, 1 = Salida
    const [pin, setPin] = useState('');
    const [currentTime, setCurrentTime] = useState('');
    const [currentDate, setCurrentDate] = useState('');

    const { data, setData, post, processing, reset, errors } = useForm({
        pin: '',
        punch_type: 0,
    });

    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            setCurrentTime(now.toLocaleTimeString('es-DO', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }));
            setCurrentDate(now.toLocaleDateString('es-DO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
        };
        updateTime();
        const interval = setInterval(updateTime, 1000);
        return () => clearInterval(interval);
    }, []);

    const handleDigit = (digit) => {
        if (pin.length < 6) {
            const newPin = pin + digit;
            setPin(newPin);
            setData('pin', newPin);
        }
    };

    const handleDelete = () => {
        const newPin = pin.slice(0, -1);
        setPin(newPin);
        setData('pin', newPin);
    };

    const handleClear = () => {
        setPin('');
        setData('pin', '');
    };

    const handleSubmit = (e) => {
        e?.preventDefault();
        if (!pin) return;

        post('/kiosk/hr-punch', {
            data: { pin, punch_type: punchType },
            onSuccess: () => {
                setPin('');
                setData('pin', '');
            }
        });
    };

    return (
        <KioskLayout title="Reloj Marcador de Personal" showBack={true} backUrl="/kiosk/dashboard">
            <Head title="Reloj Marcador - SGA" />

            <div className="max-w-5xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center py-4">
                {/* Left Column: Live Time & Punch Type Selector */}
                <div className="lg:col-span-6 space-y-6">
                    <div className="p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl text-center relative overflow-hidden shadow-2xl">
                        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

                        <div className="text-xs uppercase font-bold text-blue-400 tracking-widest mb-2">Hora Oficial Institucional</div>
                        <div className="text-6xl md:text-7xl font-mono font-black text-white tracking-wider drop-shadow-md">
                            {currentTime || '08:00:00'}
                        </div>
                        <div className="text-sm text-slate-400 capitalize mt-2 font-medium">
                            {currentDate}
                        </div>

                        {/* Entrada / Salida Toggle */}
                        <div className="grid grid-cols-2 gap-4 mt-8">
                            <button
                                type="button"
                                onClick={() => {
                                    setPunchType(0);
                                    setData('punch_type', 0);
                                }}
                                className={`py-4 rounded-2xl font-bold text-lg border transition-all flex items-center justify-center gap-2 ${
                                    punchType === 0
                                        ? 'bg-emerald-600 border-emerald-400 text-white shadow-xl shadow-emerald-600/40 ring-2 ring-emerald-400/50 scale-[1.02]'
                                        : 'bg-white/5 border-white/10 text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="fa-solid fa-right-to-bracket text-xl"></i>
                                <span>ENTRADA</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setPunchType(1);
                                    setData('punch_type', 1);
                                }}
                                className={`py-4 rounded-2xl font-bold text-lg border transition-all flex items-center justify-center gap-2 ${
                                    punchType === 1
                                        ? 'bg-amber-600 border-amber-400 text-white shadow-xl shadow-amber-600/40 ring-2 ring-amber-400/50 scale-[1.02]'
                                        : 'bg-white/5 border-white/10 text-slate-400 hover:text-white'
                                }`}
                            >
                                <i className="fa-solid fa-right-from-bracket text-xl"></i>
                                <span>SALIDA</span>
                            </button>
                        </div>
                    </div>

                    {/* Recent punches ticker */}
                    <div className="p-6 rounded-3xl bg-slate-900/40 border border-white/10 backdrop-blur-xl">
                        <div className="flex items-center justify-between mb-4">
                            <h4 className="text-xs uppercase font-bold text-slate-400 tracking-wider">Últimos Marcajes de Hoy</h4>
                            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </div>
                        <div className="space-y-2 max-h-48 overflow-y-auto pr-1">
                            {recentPunches.length === 0 ? (
                                <div className="text-xs text-slate-500 text-center py-4">Aún no hay marcajes registrados hoy</div>
                            ) : (
                                recentPunches.map(p => (
                                    <div key={p.id} className="p-3 rounded-xl bg-white/5 border border-white/5 flex items-center justify-between text-xs">
                                        <div className="flex items-center gap-2">
                                            <i className={`fa-solid ${p.type === 'Entrada' ? 'fa-arrow-right-to-bracket text-emerald-400' : 'fa-arrow-right-from-bracket text-amber-400'}`}></i>
                                            <span className="font-semibold text-white">{p.employee_name}</span>
                                        </div>
                                        <div className="flex items-center gap-2 font-mono">
                                            <span className="text-slate-400">{p.time}</span>
                                            <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${p.type === 'Entrada' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'}`}>
                                                {p.type}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {/* Right Column: Touch Numeric Keypad */}
                <div className="lg:col-span-6 p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl shadow-2xl flex flex-col items-center">
                    <div className="text-center mb-6">
                        <div className="text-xs uppercase font-bold text-slate-400 tracking-wider">Identificación de Personal</div>
                        <h3 className="text-xl font-bold text-white mt-1">Introduce tu PIN o ID Biométrico</h3>
                    </div>

                    {/* PIN Display */}
                    <div className="w-full max-w-xs h-16 rounded-2xl bg-white/5 border-2 border-blue-500/40 flex items-center justify-center text-3xl font-mono font-bold tracking-widest text-blue-300 mb-6 shadow-inner">
                        {pin ? '•'.repeat(pin.length) : <span className="text-slate-600 text-base font-normal">Toca los números abajo</span>}
                    </div>

                    {/* Keypad Grid */}
                    <div className="grid grid-cols-3 gap-3 w-full max-w-xs">
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map(num => (
                            <button
                                key={num}
                                type="button"
                                onClick={() => handleDigit(num.toString())}
                                className="h-16 rounded-2xl bg-white/10 hover:bg-white/20 active:bg-blue-600 active:scale-95 text-2xl font-bold text-white border border-white/10 transition-all flex items-center justify-center shadow-lg"
                            >
                                {num}
                            </button>
                        ))}
                        <button
                            type="button"
                            onClick={handleClear}
                            className="h-16 rounded-2xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 font-bold text-sm border border-rose-500/30 transition-all flex items-center justify-center active:scale-95"
                        >
                            C
                        </button>
                        <button
                            type="button"
                            onClick={() => handleDigit('0')}
                            className="h-16 rounded-2xl bg-white/10 hover:bg-white/20 active:bg-blue-600 active:scale-95 text-2xl font-bold text-white border border-white/10 transition-all flex items-center justify-center shadow-lg"
                        >
                            0
                        </button>
                        <button
                            type="button"
                            onClick={handleDelete}
                            className="h-16 rounded-2xl bg-white/10 hover:bg-white/20 text-slate-300 font-bold text-lg border border-white/10 transition-all flex items-center justify-center active:scale-95"
                        >
                            <i className="fa-solid fa-delete-left"></i>
                        </button>
                    </div>

                    {/* Submit Punch Button */}
                    <button
                        type="button"
                        onClick={handleSubmit}
                        disabled={processing || !pin}
                        className={`mt-6 w-full max-w-xs py-4 rounded-2xl font-bold text-lg shadow-xl transition-all flex items-center justify-center gap-2 ${
                            pin && !processing
                                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white shadow-blue-600/30 active:scale-95'
                                : 'bg-white/10 text-slate-500 cursor-not-allowed border border-white/5'
                        }`}
                    >
                        {processing ? (
                            <>
                                <i className="fa-solid fa-circle-notch fa-spin"></i>
                                <span>Verificando...</span>
                            </>
                        ) : (
                            <>
                                <i className="fa-solid fa-fingerprint text-xl"></i>
                                <span>Confirmar Marcaje</span>
                            </>
                        )}
                    </button>
                </div>
            </div>
        </KioskLayout>
    );
}
