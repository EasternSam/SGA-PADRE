import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Finances({ student, pendingPayments = [], totalPending = 0 }) {
    const [selectedPayment, setSelectedPayment] = useState(null);
    const [isPaying, setIsPaying] = useState(false);
    const [paymentSuccess, setPaymentSuccess] = useState(false);

    const handleSimulatedPayment = () => {
        setIsPaying(true);
        setTimeout(() => {
            setIsPaying(false);
            setPaymentSuccess(true);
        }, 1500);
    };

    return (
        <KioskLayout title="Estado de Cuenta & Pagos" student={student}>
            <Head title="Estado de Cuenta - Kiosco" />

            <div className="max-w-5xl mx-auto w-full space-y-6">
                {/* Summary Banner */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="md:col-span-2 p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl relative overflow-hidden flex flex-col justify-between">
                        <div className="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div>
                            <span className="px-3.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 uppercase tracking-wider">
                                Balance de Matrícula
                            </span>
                            <div className="text-4xl md:text-5xl font-black text-white mt-4 tracking-tight">
                                RD$ {totalPending.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                            </div>
                            <p className="text-slate-400 text-sm mt-2">
                                {totalPending > 0
                                    ? 'Tienes pagos pendientes requeridos para mantener activa la selección y exámenes.'
                                    : '¡Estás al día! No registras cargos pendientes actualmente.'}
                            </p>
                        </div>
                        <div className="mt-6 flex items-center gap-3">
                            <span className={`w-3 h-3 rounded-full ${totalPending > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'}`}></span>
                            <span className="text-sm font-semibold text-slate-300">
                                Estado Financiero: {totalPending > 0 ? 'Balances por regularizar' : 'Solvente y Habilitado'}
                            </span>
                        </div>
                    </div>

                    <div className="p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl flex flex-col justify-between">
                        <div>
                            <div className="text-xs uppercase font-bold text-slate-400 tracking-wider">Estudiante</div>
                            <div className="text-xl font-bold text-white mt-1">{student?.name || 'Aspirante / Estudiante'}</div>
                            <div className="text-xs font-mono text-blue-400 mt-1">{student?.student_code}</div>
                            <div className="text-xs text-slate-400 mt-2">{student?.career}</div>
                        </div>

                        <button
                            onClick={() => window.print()}
                            className="mt-6 w-full py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 text-white font-semibold text-sm border border-white/15 transition-all flex items-center justify-center gap-2"
                        >
                            <i className="fa-solid fa-print"></i>
                            <span>Imprimir Estado</span>
                        </button>
                    </div>
                </div>

                {/* Payments Table / List */}
                <div className="rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl overflow-hidden shadow-2xl">
                    <div className="p-6 border-b border-white/10 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                                <i className="fa-solid fa-receipt"></i>
                            </div>
                            <h3 className="text-lg font-bold text-white">Detalle de Cuotas & Servicios</h3>
                        </div>
                        <span className="text-xs text-slate-400 font-mono">{pendingPayments.length} concepto(s)</span>
                    </div>

                    {pendingPayments.length === 0 ? (
                        <div className="p-12 text-center text-slate-400">
                            <i className="fa-regular fa-circle-check text-5xl text-emerald-400 mb-3"></i>
                            <div className="text-lg font-semibold text-white">No tienes cargos pendientes</div>
                            <p className="text-sm mt-1">Tu cuenta está al día con el departamento de finanzas.</p>
                        </div>
                    ) : (
                        <div className="divide-y divide-white/5">
                            {pendingPayments.map((p) => (
                                <div key={p.id} className="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-white/[0.02] transition-colors">
                                    <div className="space-y-1">
                                        <div className="font-bold text-white text-lg">{p.concept}</div>
                                        <div className="flex items-center gap-4 text-xs text-slate-400">
                                            <span><i className="fa-regular fa-calendar mr-1 text-slate-500"></i> Vence: {p.due_date}</span>
                                            <span className="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-medium border border-amber-500/30">
                                                {p.status}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-6 self-end sm:self-center">
                                        <div className="text-right">
                                            <div className="text-xl font-mono font-black text-emerald-400">
                                                RD$ {p.amount.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                            </div>
                                            <div className="text-[11px] text-slate-500">Impuestos incl.</div>
                                        </div>
                                        <button
                                            onClick={() => setSelectedPayment(p)}
                                            className="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 flex items-center gap-2"
                                        >
                                            <i className="fa-solid fa-credit-card"></i>
                                            <span>Pagar Aquí</span>
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Touch Payment Modal */}
                {selectedPayment && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
                        <div className="bg-slate-900 border border-white/20 rounded-3xl max-w-lg w-full p-8 shadow-2xl relative">
                            {!paymentSuccess ? (
                                <>
                                    <div className="flex items-center justify-between pb-4 border-b border-white/10">
                                        <h4 className="text-xl font-bold text-white">Terminal de Pago Táctil</h4>
                                        <button
                                            onClick={() => setSelectedPayment(null)}
                                            className="w-10 h-10 rounded-full bg-white/10 text-slate-400 hover:text-white flex items-center justify-center text-lg"
                                        >
                                            &times;
                                        </button>
                                    </div>

                                    <div className="my-6 space-y-4">
                                        <div className="p-4 rounded-2xl bg-white/5 border border-white/10">
                                            <div className="text-xs text-slate-400">Concepto a pagar:</div>
                                            <div className="text-lg font-bold text-white">{selectedPayment.concept}</div>
                                            <div className="text-2xl font-mono font-black text-emerald-400 mt-2">
                                                RD$ {selectedPayment.amount.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                            </div>
                                        </div>

                                        <div className="p-5 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-blue-200 text-sm flex items-start gap-3">
                                            <i className="fa-solid fa-tower-broadcast text-xl text-blue-400 mt-0.5 animate-pulse"></i>
                                            <div>
                                                <div className="font-semibold text-white">Inserta o acerca tu tarjeta (NFC / Chip)</div>
                                                <div className="text-xs text-slate-300 mt-1">El lector integrado de la terminal procesará la transacción con Cardnet / Azul.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex gap-4">
                                        <button
                                            onClick={() => setSelectedPayment(null)}
                                            className="flex-1 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm transition-all"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            onClick={handleSimulatedPayment}
                                            disabled={isPaying}
                                            className="flex-1 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2"
                                        >
                                            {isPaying ? (
                                                <>
                                                    <i className="fa-solid fa-circle-notch fa-spin"></i>
                                                    <span>Procesando...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fa-solid fa-check"></i>
                                                    <span>Confirmar Pago</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </>
                            ) : (
                                <div className="text-center py-6">
                                    <div className="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-4xl mx-auto mb-4">
                                        <i className="fa-solid fa-check"></i>
                                    </div>
                                    <h4 className="text-2xl font-bold text-white">¡Transacción Aprobada!</h4>
                                    <p className="text-slate-400 text-sm mt-2">
                                        El pago de <strong>{selectedPayment.concept}</strong> se procesó satisfactoriamente. Imprimiendo comprobante fiscal...
                                    </p>
                                    <div className="mt-8">
                                        <button
                                            onClick={() => {
                                                setSelectedPayment(null);
                                                setPaymentSuccess(false);
                                            }}
                                            className="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-base shadow-xl"
                                        >
                                            Finalizar & Retirar Comprobante
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>, document.body)
                )}
            </div>
        </KioskLayout>
    );
}
