import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Login() {
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
            if (data.pin.length < 4) {
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

    const handleSubmit = (e) => {
        e?.preventDefault();
        post('/kiosk/login');
    };

    return (
        <KioskLayout title="Acceso al Kiosco de Autoservicio" showBack={false}>
            <Head title="Ingreso al Kiosco - SGA" />

            <div className="max-w-4xl mx-auto w-full grid grid-cols-1 md:grid-cols-12 gap-8 items-center py-4">
                {/* Left Side: Instructions & Input Display */}
                <div className="md:col-span-6 space-y-6">
                    <div>
                        <span className="px-3.5 py-1.5 rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold border border-blue-500/30 uppercase tracking-widest">
                            Terminal Táctil
                        </span>
                        <h2 className="text-3xl font-black text-white mt-3">Identifícate para Comenzar</h2>
                        <p className="text-slate-400 text-sm mt-1">
                            Toca los campos para escribir con el teclado numérico interactivo.
                        </p>
                    </div>

                    <div className="space-y-4">
                        {/* Document Field */}
                        <div
                            onClick={() => setFocusedInput('document_id')}
                            className={`p-5 rounded-2xl border-2 transition-all cursor-pointer ${
                                focusedInput === 'document_id'
                                    ? 'bg-blue-600/10 border-blue-500 ring-2 ring-blue-500/30 shadow-lg'
                                    : 'bg-white/5 border-white/10 hover:border-white/20'
                            }`}
                        >
                            <div className="text-xs uppercase font-bold text-slate-400">Cédula o Matrícula</div>
                            <div className="text-2xl font-mono font-bold text-white mt-1 h-8 flex items-center">
                                {data.document_id || <span className="text-slate-600 font-sans font-normal text-base">Ej: 40212345678 o 2024-0012</span>}
                            </div>
                        </div>

                        {/* PIN Field */}
                        <div
                            onClick={() => setFocusedInput('pin')}
                            className={`p-5 rounded-2xl border-2 transition-all cursor-pointer ${
                                focusedInput === 'pin'
                                    ? 'bg-blue-600/10 border-blue-500 ring-2 ring-blue-500/30 shadow-lg'
                                    : 'bg-white/5 border-white/10 hover:border-white/20'
                            }`}
                        >
                            <div className="text-xs uppercase font-bold text-slate-400">PIN de Seguridad (4 dígitos)</div>
                            <div className="text-2xl font-mono font-bold text-blue-300 mt-1 h-8 flex items-center tracking-widest">
                                {data.pin ? '•'.repeat(data.pin.length) : <span className="text-slate-600 font-sans font-normal text-base">Toca para teclear tu PIN</span>}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 text-xs text-slate-400">
                        <i className="fa-solid fa-circle-info text-blue-400 text-base"></i>
                        <span>Si no tienes un PIN asignado, solicítalo en la Dirección de Registro o Caja.</span>
                    </div>

                    <div className="pt-2">
                        <Link
                            href="/kiosk/hr-punch"
                            className="inline-flex items-center gap-2 text-sm text-blue-400 hover:text-blue-300 font-semibold"
                        >
                            <i className="fa-solid fa-fingerprint"></i>
                            <span>¿Solo necesitas registrar tu asistencia? Clic aquí</span>
                        </Link>
                    </div>
                </div>

                {/* Right Side: Virtual Numpad */}
                <div className="md:col-span-6 p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl shadow-2xl flex flex-col items-center">
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

                    <button
                        type="button"
                        onClick={handleSubmit}
                        disabled={processing || !data.document_id || !data.pin}
                        className={`mt-6 w-full max-w-xs py-4 rounded-2xl font-bold text-lg shadow-xl transition-all flex items-center justify-center gap-2 ${
                            data.document_id && data.pin && !processing
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
                                <i className="fa-solid fa-right-to-bracket text-xl"></i>
                                <span>Ingresar al Kiosco</span>
                            </>
                        )}
                    </button>
                </div>
            </div>
        </KioskLayout>
    );
}
