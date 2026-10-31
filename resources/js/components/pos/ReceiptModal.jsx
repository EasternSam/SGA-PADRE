import React from 'react';
import { createPortal } from 'react-dom';
import { X, Printer, CheckCircle2, AlertTriangle, Check, ArrowRight } from 'lucide-react';
import { formatMoney } from './utils';

export default function ReceiptModal({
    isOpen,
    onClose,
    receipt,
    issuer = {},
}) {
    if (!isOpen || !receipt) return null;

    const handlePrint = () => {
        window.print();
    };

    const isPendingNcf = receipt.ncf_status === 'pending' || !receipt.ncf;

    return (
        createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm print:p-0 print:bg-white">
            <div className="relative w-full max-w-md rounded-2xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] shadow-2xl overflow-hidden print:border-none print:shadow-none print:max-w-none print:w-full">
                {/* Cabecera del modal (oculta en impresión) */}
                <div className="flex items-center justify-between border-b border-neutral-100 dark:border-[#262626] px-5 py-3.5 bg-neutral-50/60 dark:bg-[#1f1f1f]/50 print:hidden">
                    <div className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold text-sm">
                        <CheckCircle2 className="h-4 w-4" />
                        <span>Transacción Completada</span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-1 text-neutral-400 hover:bg-neutral-200 dark:hover:bg-[#262626] hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>

                {/* Cuerpo del Recibo Térmico */}
                <div id="pos-thermal-receipt" className="p-6 font-mono text-xs text-neutral-800 dark:text-neutral-200 space-y-4 print:text-black">
                    {/* Encabezado Institucional */}
                    <div className="text-center space-y-1">
                        <h2 className="text-sm font-extrabold tracking-wide text-neutral-900 dark:text-neutral-100 uppercase">
                            {issuer.name || 'Academic+'}
                        </h2>
                        {issuer.rnc && (
                            <p className="text-[11px] text-neutral-500 dark:text-neutral-400">
                                RNC: {issuer.rnc}
                            </p>
                        )}
                        {issuer.address && (
                            <p className="text-[11px] text-neutral-500 dark:text-neutral-400">
                                {issuer.address}
                            </p>
                        )}
                        <p className="text-[11px] text-neutral-500 dark:text-neutral-400">
                            República Dominicana
                        </p>
                    </div>

                    <div className="border-t border-dashed border-neutral-300 dark:border-[#333333] pt-3 space-y-1">
                        <div className="flex justify-between">
                            <span className="text-neutral-500">Recibo No:</span>
                            <span className="font-bold">#{String(receipt.id).padStart(6, '0')}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-neutral-500">Fecha y hora:</span>
                            <span>{receipt.date}</span>
                        </div>
                        {receipt.cashier && (
                            <div className="flex justify-between">
                                <span className="text-neutral-500">Cajero(a):</span>
                                <span>{receipt.cashier}</span>
                            </div>
                        )}
                    </div>

                    {/* Datos del Cliente */}
                    <div className="border-t border-dashed border-neutral-300 dark:border-[#333333] pt-3 space-y-1">
                        <div className="flex justify-between">
                            <span className="text-neutral-500">Cliente:</span>
                            <span className="font-semibold text-right max-w-[200px] truncate">
                                {receipt.customer?.name || 'Cliente General'}
                            </span>
                        </div>
                        {receipt.customer?.code && (
                            <div className="flex justify-between">
                                <span className="text-neutral-500">Matrícula:</span>
                                <span>{receipt.customer.code}</span>
                            </div>
                        )}
                        {receipt.customer?.cedula && (
                            <div className="flex justify-between">
                                <span className="text-neutral-500">Cédula:</span>
                                <span>{receipt.customer.cedula}</span>
                            </div>
                        )}
                        {receipt.rnc_client && (
                            <div className="flex justify-between">
                                <span className="text-neutral-500">RNC Cliente:</span>
                                <span className="font-bold">{receipt.rnc_client}</span>
                            </div>
                        )}
                        {receipt.company_name && (
                            <div className="flex justify-between">
                                <span className="text-neutral-500">Empresa:</span>
                                <span className="text-right max-w-[180px] truncate">{receipt.company_name}</span>
                            </div>
                        )}
                    </div>

                    {/* Datos de Comprobante Fiscal */}
                    <div className="border-t border-dashed border-neutral-300 dark:border-[#333333] pt-3 space-y-1">
                        <div className="flex justify-between items-center">
                            <span className="text-neutral-500">Tipo de NCF:</span>
                            <span className="font-semibold">{receipt.ncf_kind === 'B01' ? 'Crédito Fiscal (B01)' : 'Consumo (B02)'}</span>
                        </div>
                        <div className="flex justify-between items-center">
                            <span className="text-neutral-500">NCF:</span>
                            {isPendingNcf ? (
                                <span className="rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 text-[10px] font-bold">
                                    NCF Pendiente
                                </span>
                            ) : (
                                <span className="font-bold tracking-wider">{receipt.ncf}</span>
                            )}
                        </div>
                        {receipt.security_code && (
                            <div className="flex justify-between">
                                <span className="text-neutral-500">Cód. Seguridad:</span>
                                <span>{receipt.security_code}</span>
                            </div>
                        )}
                    </div>

                    {/* Detalle de Concepto */}
                    <div className="border-t border-dashed border-neutral-300 dark:border-[#333333] pt-3 space-y-2">
                        <div className="flex justify-between text-neutral-500 text-[11px] pb-1 border-b border-neutral-200/50 dark:border-[#262626]">
                            <span>DESCRIPCIÓN</span>
                            <span>TOTAL</span>
                        </div>
                        <div className="space-y-1">
                            <div className="flex justify-between font-semibold">
                                <span className="max-w-[220px] truncate">{receipt.concept}</span>
                                <span>{formatMoney(receipt.subtotal)}</span>
                            </div>
                            <div className="text-[11px] text-neutral-500">
                                Cantidad: {receipt.quantity}
                            </div>
                        </div>
                    </div>

                    {/* Totales y Pago */}
                    <div className="border-t border-dashed border-neutral-300 dark:border-[#333333] pt-3 space-y-1.5">
                        <div className="flex justify-between">
                            <span className="text-neutral-500">Subtotal:</span>
                            <span>{formatMoney(receipt.subtotal)}</span>
                        </div>
                        {receipt.discount_amount > 0 && (
                            <div className="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span>Descuento:</span>
                                <span>-{formatMoney(receipt.discount_amount)}</span>
                            </div>
                        )}
                        <div className="flex justify-between font-bold text-sm text-neutral-900 dark:text-neutral-100 pt-1 border-t border-neutral-200/60 dark:border-[#262626]">
                            <span>TOTAL PAGADO:</span>
                            <span>{formatMoney(receipt.total)}</span>
                        </div>
                        <div className="flex justify-between text-[11px] pt-1 text-neutral-500">
                            <span>Forma de Pago:</span>
                            <span>{receipt.gateway}</span>
                        </div>
                        {receipt.cash_received !== null && (
                            <>
                                <div className="flex justify-between text-[11px] text-neutral-500">
                                    <span>Efectivo Recibido:</span>
                                    <span>{formatMoney(receipt.cash_received)}</span>
                                </div>
                                <div className="flex justify-between text-[11px] font-semibold text-neutral-700 dark:text-neutral-300">
                                    <span>Cambio Entregado:</span>
                                    <span>{formatMoney(receipt.change_amount || 0)}</span>
                                </div>
                            </>
                        )}
                    </div>

                    {/* Pie de Recibo */}
                    <div className="border-t border-dashed border-neutral-300 dark:border-[#333333] pt-4 text-center space-y-1">
                        <p className="text-[11px] font-semibold text-neutral-600 dark:text-neutral-400">
                            ¡Gracias por su pago!
                        </p>
                        <p className="text-[10px] text-neutral-400">
                            Conserve este comprobante para sus registros.
                        </p>
                    </div>
                </div>

                {/* Acciones del Modal (ocultas en impresión) */}
                <div className="flex items-center justify-end gap-2 border-t border-neutral-100 dark:border-[#262626] p-4 bg-neutral-50/50 dark:bg-[#1f1f1f]/50 print:hidden">
                    <button
                        type="button"
                        onClick={handlePrint}
                        className="flex items-center gap-1.5 rounded-xl border border-neutral-300 dark:border-[#333333] hover:bg-neutral-100 dark:hover:bg-[#262626] px-4 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-300 transition-colors"
                    >
                        <Printer className="h-4 w-4" />
                        <span>Imprimir</span>
                    </button>
                    <button
                        type="button"
                        onClick={onClose}
                        className="flex items-center gap-1.5 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-xs font-bold text-white transition-colors"
                    >
                        <span>Nuevo Cobro</span>
                        <ArrowRight className="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>, document.body)
    );
}
