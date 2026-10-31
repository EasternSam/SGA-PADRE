import React from 'react';
import { createPortal } from 'react-dom';
import { X, History, DollarSign, Receipt, ChevronRight } from 'lucide-react';
import { formatMoney } from './utils';

export default function ShiftDrawer({
    isOpen,
    onClose,
    todayPayments = [],
    todayTotal = 0,
    onSelectReceipt,
}) {
    if (!isOpen) return null;

    return (
        createPortal(<div className="fixed inset-0 z-50 overflow-hidden bg-black/40 backdrop-blur-xs">
            <div className="absolute inset-y-0 right-0 flex max-w-full pl-10">
                <div className="w-screen max-w-md border-l border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] shadow-xl flex flex-col">
                    {/* Encabezado del Drawer */}
                    <div className="flex items-center justify-between border-b border-neutral-200 dark:border-[#262626] px-5 py-4">
                        <div className="flex items-center gap-2.5">
                            <History className="h-5 w-5 text-brand-primary" />
                            <div>
                                <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                    Cierre y Movimientos del Turno
                                </h3>
                            </div>
                        </div>
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label="Cerrar turno"
                            className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-100 dark:hover:bg-[#262626] hover:text-neutral-700 dark:text-neutral-200"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>

                    {/* Resumen Superior */}
                    <div className="p-5 border-b border-neutral-100 dark:border-[#262626] bg-neutral-50/50 dark:bg-[#1f1f1f]/40">
                        <div className="grid grid-cols-2 gap-3">
                            <div className="rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-3">
                                <span className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Total Recaudado
                                </span>
                                <div className="text-base font-extrabold text-brand-primary mt-0.5">
                                    {formatMoney(todayTotal)}
                                </div>
                            </div>
                            <div className="rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-3">
                                <span className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Transacciones
                                </span>
                                <div className="text-base font-extrabold text-neutral-900 dark:text-neutral-100 mt-0.5">
                                    {todayPayments.length}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Lista de Transacciones de Hoy */}
                    <div className="flex-1 overflow-y-auto p-4 space-y-2">
                        {todayPayments.length === 0 ? (
                            <div className="py-12 text-center text-xs text-neutral-400">
                                No se han registrado cobros el día de hoy
                            </div>
                        ) : (
                            todayPayments.map((p) => (
                                <button
                                    key={p.id}
                                    type="button"
                                    onClick={() => onSelectReceipt(p)}
                                    className="w-full text-left rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#1f1f1f] p-3 hover:border-neutral-300 dark:hover:border-[#333333] hover:shadow-xs transition-all flex items-center justify-between group"
                                >
                                    <div className="min-w-0 pr-2">
                                        <div className="flex items-center gap-2">
                                            <span className="font-semibold text-xs text-neutral-900 dark:text-neutral-100 truncate">
                                                {p.customer?.name || 'Cliente General'}
                                            </span>
                                            <span className="text-[10px] text-neutral-400">
                                                {p.time || p.date}
                                            </span>
                                        </div>
                                        <div className="text-xs text-neutral-500 dark:text-neutral-400 truncate mt-0.5">
                                            {p.concept} • {p.gateway}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 shrink-0">
                                        <span className="font-bold text-xs text-neutral-900 dark:text-neutral-100">
                                            {formatMoney(p.total)}
                                        </span>
                                        <ChevronRight className="h-4 w-4 text-neutral-400 group-hover:text-neutral-600 dark:group-hover:text-neutral-200 transition-transform group-hover:translate-x-0.5" />
                                    </div>
                                </button>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </div>, document.body)
    );
}
