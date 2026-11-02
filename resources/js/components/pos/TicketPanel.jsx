import React from 'react';
import {
    Banknote,
    CreditCard,
    Building2,
    Plus,
    Minus,
    AlertCircle,
    Receipt,
    Check,
    Loader2,
} from 'lucide-react';
import { formatMoney } from './utils';
import PoweredByBills from '@/components/bills/PoweredByBills';

export default function TicketPanel({
    selectedConcept,
    quantity,
    onChangeQuantity,
    maxQuantity = 99,
    unitPrice,
    subtotal,
    discount,
    onChangeDiscount,
    total,
    paymentMethod,
    onChangePaymentMethod,
    gateways = ['Efectivo', 'Tarjeta', 'Transferencia'],
    receivedCash,
    onChangeReceivedCash,
    change,
    ncfType,
    onChangeNcfType,
    rnc,
    onChangeRnc,
    companyName,
    onChangeCompanyName,
    notes,
    onChangeNotes,
    onProcessPayment,
    processing,
    error,
    selectedStudent,
    isWalkIn,
}) {
    const cashValue = Number(receivedCash) || 0;
    const isCash = paymentMethod === 'Efectivo';
    const isCreditFiscal = ncfType === 'B01';

    const handleAddCash = (amount) => {
        onChangeReceivedCash(String(cashValue + amount));
    };

    const handleExactCash = () => {
        onChangeReceivedCash(String(total));
    };

    // Validación básica para habilitar botón de cobro
    const canCheckout =
        Boolean(selectedConcept) &&
        (Boolean(selectedStudent) || Boolean(isWalkIn)) &&
        total > 0 &&
        (!isCash || cashValue >= total) &&
        (!isCreditFiscal || (rnc && (rnc.length === 9 || rnc.length === 11))) &&
        !processing;

    return (
        <div className="flex flex-col lg:h-[calc(100vh-195px)] lg:max-h-[calc(100vh-195px)] min-h-[500px] rounded-2xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] shadow-sm overflow-hidden">
            {/* Header del Ticket */}
            <div className="shrink-0 flex items-center justify-between border-b border-neutral-200 dark:border-[#262626] px-4 py-3 bg-neutral-50/50 dark:bg-[#1f1f1f]/40">
                <div className="flex items-center gap-2">
                    <Receipt className="h-4 w-4 text-brand-primary" />
                    <h3 className="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">
                        Ticket de Cobro
                    </h3>
                </div>
                <div className="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-[180px]">
                    {isWalkIn ? 'Cliente General' : selectedStudent?.name || 'Sin cliente'}
                </div>
            </div>

            {/* Contenido con Scroll Interno Controlado */}
            <div className="flex-1 overflow-y-auto px-4 py-3 space-y-4 pr-2">
                {/* Concepto seleccionado y Cantidad */}
                {selectedConcept ? (
                    <div className="rounded-xl border border-neutral-200 dark:border-[#262626] p-3 bg-neutral-50/50 dark:bg-[#1f1f1f]/50 space-y-3">
                        <div className="flex items-start justify-between gap-2">
                            <div className="min-w-0">
                                <div className="text-xs font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                    {selectedConcept.name}
                                </div>
                                <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Unitario: {formatMoney(unitPrice)}
                                </div>
                            </div>
                            <div className="text-sm font-bold text-neutral-900 dark:text-neutral-100 shrink-0">
                                {formatMoney(subtotal)}
                            </div>
                        </div>

                        {/* Control de cantidad */}
                        <div className="flex items-center justify-between pt-2 border-t border-neutral-200/60 dark:border-[#262626]">
                            <span className="text-xs text-neutral-600 dark:text-neutral-400">Cantidad:</span>
                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={() => onChangeQuantity(Math.max(1, quantity - 1))}
                                    disabled={quantity <= 1}
                                    className="h-6 w-6 rounded-md border border-neutral-200 dark:border-[#333333] flex items-center justify-center text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-[#262626] disabled:opacity-40"
                                >
                                    <Minus className="h-3 w-3" />
                                </button>
                                <span className="text-xs font-semibold text-neutral-900 dark:text-neutral-100 w-6 text-center">
                                    {quantity}
                                </span>
                                <button
                                    type="button"
                                    onClick={() => onChangeQuantity(Math.min(maxQuantity, quantity + 1))}
                                    disabled={quantity >= maxQuantity}
                                    className="h-6 w-6 rounded-md border border-neutral-200 dark:border-[#333333] flex items-center justify-center text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-[#262626] disabled:opacity-40"
                                >
                                    <Plus className="h-3 w-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="rounded-xl border border-dashed border-neutral-200 dark:border-[#262626] p-6 text-center text-xs text-neutral-400">
                        Selecciona un concepto del catálogo para armar el cobro
                    </div>
                )}

                {/* Descuento Aplicado */}
                <div className="flex items-center justify-between gap-3 text-xs">
                    <label className="text-neutral-600 dark:text-neutral-400 font-medium">
                        Descuento (RD$):
                    </label>
                    <input
                        type="number"
                        min="0"
                        max={subtotal}
                        step="1"
                        value={discount || ''}
                        onChange={(e) => onChangeDiscount(Math.min(subtotal, Math.max(0, Number(e.target.value) || 0)))}
                        placeholder="0.00"
                        className="w-28 rounded-lg border border-neutral-200 dark:border-[#262626] bg-neutral-50 dark:bg-[#1f1f1f] px-2.5 py-1.5 text-right font-semibold text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    />
                </div>

                {/* Método de Pago */}
                <div className="space-y-1.5">
                    <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Método de pago
                    </label>
                    <div className="grid grid-cols-3 gap-1.5">
                        {gateways.map((g) => {
                            const isSelected = paymentMethod === g;
                            return (
                                <button
                                    key={g}
                                    type="button"
                                    onClick={() => onChangePaymentMethod(g)}
                                    className={`flex flex-col items-center justify-center gap-1 rounded-xl border p-2 text-xs font-medium transition-all ${
                                        isSelected
                                            ? 'border-brand-primary bg-brand-primary/10 text-brand-primary font-semibold'
                                            : 'border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#1f1f1f] text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-[#262626]'
                                    }`}
                                >
                                    {g === 'Efectivo' && <Banknote className="h-4 w-4" />}
                                    {g === 'Tarjeta' && <CreditCard className="h-4 w-4" />}
                                    {g === 'Transferencia' && <Building2 className="h-4 w-4" />}
                                    <span>{g}</span>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* Calculadora de Efectivo si aplica */}
                {isCash && (
                    <div className="rounded-xl border border-neutral-200 dark:border-[#262626] bg-neutral-50/50 dark:bg-[#1f1f1f]/50 p-3 space-y-2.5">
                        <div className="flex items-center justify-between text-xs">
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">
                                Efectivo entregado:
                            </span>
                            <input
                                type="number"
                                min="0"
                                step="1"
                                value={receivedCash}
                                onChange={(e) => onChangeReceivedCash(e.target.value)}
                                placeholder="0.00"
                                className="w-28 rounded-lg border border-neutral-300 dark:border-[#333333] bg-white dark:bg-[#171717] px-2.5 py-1 text-right text-xs font-bold text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                            />
                        </div>

                        {/* Botones de efectivo rápido */}
                        <div className="flex items-center gap-1.5 overflow-x-auto text-[11px]">
                            <button
                                type="button"
                                onClick={() => handleAddCash(100)}
                                className="rounded-md bg-neutral-200 dark:bg-[#262626] px-2 py-1 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-[#333333]"
                            >
                                +100
                            </button>
                            <button
                                type="button"
                                onClick={() => handleAddCash(500)}
                                className="rounded-md bg-neutral-200 dark:bg-[#262626] px-2 py-1 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-[#333333]"
                            >
                                +500
                            </button>
                            <button
                                type="button"
                                onClick={() => handleAddCash(1000)}
                                className="rounded-md bg-neutral-200 dark:bg-[#262626] px-2 py-1 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-[#333333]"
                            >
                                +1,000
                            </button>
                            <button
                                type="button"
                                onClick={() => handleAddCash(2000)}
                                className="rounded-md bg-neutral-200 dark:bg-[#262626] px-2 py-1 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-[#333333]"
                            >
                                +2,000
                            </button>
                            <button
                                type="button"
                                onClick={handleExactCash}
                                className="rounded-md bg-brand-primary/15 text-brand-primary font-semibold px-2 py-1 hover:bg-brand-primary/25"
                            >
                                Exacto
                            </button>
                        </div>

                        {/* Cambio devuelto */}
                        <div className="flex items-center justify-between pt-2 border-t border-neutral-200/60 dark:border-[#262626] text-xs">
                            <span className="text-neutral-600 dark:text-neutral-400">Cambio:</span>
                            <span className={`font-bold ${change >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'}`}>
                                {change >= 0 ? formatMoney(change) : `Faltan ${formatMoney(Math.abs(change))}`}
                            </span>
                        </div>
                    </div>
                )}

                {/* Tipo de Comprobante Fiscal (DGII) */}
                <div className="space-y-2 pt-1 border-t border-neutral-100 dark:border-[#262626]">
                    <div className="flex items-center justify-between text-xs">
                        <span className="font-medium text-neutral-700 dark:text-neutral-300">
                            Comprobante Fiscal
                        </span>
                        <div className="flex items-center rounded-lg bg-neutral-100 dark:bg-[#1f1f1f] p-0.5">
                            <button
                                type="button"
                                onClick={() => onChangeNcfType('B02')}
                                className={`rounded-md px-2.5 py-1 text-xs font-medium transition-colors ${
                                    ncfType === 'B02'
                                        ? 'bg-white dark:bg-[#262626] text-neutral-900 dark:text-neutral-100 shadow-sm'
                                        : 'text-neutral-500 dark:text-neutral-400'
                                }`}
                            >
                                Consumo (B02)
                            </button>
                            <button
                                type="button"
                                onClick={() => onChangeNcfType('B01')}
                                className={`rounded-md px-2.5 py-1 text-xs font-medium transition-colors ${
                                    ncfType === 'B01'
                                        ? 'bg-white dark:bg-[#262626] text-neutral-900 dark:text-neutral-100 shadow-sm'
                                        : 'text-neutral-500 dark:text-neutral-400'
                                }`}
                            >
                                Crédito Fiscal (B01)
                            </button>
                        </div>
                    </div>

                    {isCreditFiscal && (
                        <div className="rounded-xl border border-neutral-200 dark:border-[#262626] bg-neutral-50/50 dark:bg-[#1f1f1f]/50 p-2.5 space-y-2 text-xs">
                            <div>
                                <label className="block text-neutral-600 dark:text-neutral-400 font-medium mb-1">
                                    RNC o Cédula de la empresa *
                                </label>
                                <input
                                    type="text"
                                    value={rnc}
                                    onChange={(e) => onChangeRnc(e.target.value.replace(/\D/g, ''))}
                                    placeholder="9 u 11 dígitos sin guiones"
                                    className="w-full rounded-lg border border-neutral-300 dark:border-[#333333] bg-white dark:bg-[#171717] px-2.5 py-1 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                                />
                            </div>
                            <div>
                                <label className="block text-neutral-600 dark:text-neutral-400 font-medium mb-1">
                                    Razón Social
                                </label>
                                <input
                                    type="text"
                                    value={companyName}
                                    onChange={(e) => onChangeCompanyName(e.target.value)}
                                    placeholder="Nombre fiscal o comercial"
                                    className="w-full rounded-lg border border-neutral-300 dark:border-[#333333] bg-white dark:bg-[#171717] px-2.5 py-1 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                                />
                            </div>
                        </div>
                    )}
                </div>

                {/* Notas adicionales */}
                <div>
                    <input
                        type="text"
                        value={notes}
                        onChange={(e) => onChangeNotes(e.target.value)}
                        placeholder="Nota o referencia opcional..."
                        className="w-full rounded-lg border border-neutral-200 dark:border-[#262626] bg-neutral-50 dark:bg-[#1f1f1f] px-3 py-1.5 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    />
                </div>

                {/* Banner de error inline */}
                {error && (
                    <div className="rounded-xl border border-rose-200 dark:border-rose-900/40 bg-rose-50 dark:bg-rose-950/20 p-2.5 text-xs text-rose-700 dark:text-rose-400 flex items-start gap-2">
                        <AlertCircle className="h-4 w-4 shrink-0 mt-0.5" />
                        <span>{error}</span>
                    </div>
                )}
            </div>

            {/* Footer Fijo con Total y Acción de Cobro (NUNCA queda fuera de pantalla) */}
            <div className="shrink-0 border-t border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] px-4 py-3 space-y-2.5 shadow-sm">
                <div className="space-y-1">
                    <div className="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                        <span>Subtotal:</span>
                        <span>{formatMoney(subtotal)}</span>
                    </div>
                    {discount > 0 && (
                        <div className="flex items-center justify-between text-xs text-emerald-600 dark:text-emerald-400">
                            <span>Descuento:</span>
                            <span>-{formatMoney(discount)}</span>
                        </div>
                    )}
                    <div className="flex items-center justify-between text-base font-extrabold text-neutral-900 dark:text-neutral-100 pt-1 border-t border-neutral-100 dark:border-[#262626]">
                        <span>Total a Cobrar:</span>
                        <span className="text-brand-primary text-lg">{formatMoney(total)}</span>
                    </div>
                </div>

                <button
                    type="button"
                    onClick={onProcessPayment}
                    disabled={!canCheckout}
                    className="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark text-white py-3 text-sm font-bold shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                >
                    {processing ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            <span>Procesando cobro...</span>
                        </>
                    ) : (
                        <>
                            <Check className="h-4 w-4 stroke-[3]" />
                            <span>Registrar Cobro</span>
                        </>
                    )}
                </button>

                <PoweredByBills variant="receipt" />
            </div>
        </div>
    );
}
