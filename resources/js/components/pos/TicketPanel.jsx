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
    Clock,
    FilePlus2,
    X,
} from 'lucide-react';
import { formatMoney } from './utils';
import PoweredByBills from '@/components/bills/PoweredByBills';

export default function TicketPanel({
    operationType = 'charge',
    onChangeOperationType,
    dueDate = '',
    onChangeDueDate,
    activePendingDebt = null,
    onClearPendingDebt,
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
    const isDebtMode = operationType === 'debt';
    const cashValue = Number(receivedCash) || 0;
    const isCash = paymentMethod === 'Efectivo';
    const isCreditFiscal = ncfType === 'B01';

    const handleAddCash = (amount) => {
        onChangeReceivedCash(String(cashValue + amount));
    };

    const handleExactCash = () => {
        onChangeReceivedCash(String(total));
    };

    const setQuickDueDate = (daysToAdd) => {
        if (!onChangeDueDate) return;
        const d = new Date();
        if (daysToAdd === 'eom') {
            const eom = new Date(d.getFullYear(), d.getMonth() + 1, 0);
            onChangeDueDate(eom.toISOString().split('T')[0]);
        } else {
            d.setDate(d.getDate() + daysToAdd);
            onChangeDueDate(d.toISOString().split('T')[0]);
        }
    };

    // Validación para habilitar botón de cobro o generación de deuda
    const canCheckout = isDebtMode
        ? Boolean(selectedConcept) &&
          Boolean(selectedStudent) &&
          !isWalkIn &&
          total > 0 &&
          !processing
        : Boolean(selectedConcept) &&
          (Boolean(selectedStudent) || Boolean(isWalkIn)) &&
          total > 0 &&
          (!isCash || cashValue >= total) &&
          (!isCreditFiscal || (rnc && (rnc.length === 9 || rnc.length === 11))) &&
          !processing;

    return (
        <div className="flex flex-col lg:h-[calc(100vh-195px)] lg:max-h-[calc(100vh-195px)] min-h-[500px] rounded-2xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] shadow-sm overflow-hidden">
            {/* Header del Ticket */}
            <div className="shrink-0 border-b border-neutral-200 dark:border-[#262626] px-4 py-3 bg-neutral-50/50 dark:bg-[#1f1f1f]/40 space-y-2.5">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        {isDebtMode ? (
                            <FilePlus2 className="h-4 w-4 text-amber-600 dark:text-amber-400" />
                        ) : (
                            <Receipt className="h-4 w-4 text-brand-primary" />
                        )}
                        <h3 className="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">
                            {isDebtMode ? 'Generar Deuda (CxC)' : 'Ticket de Cobro'}
                        </h3>
                    </div>
                    <div className="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-[180px]">
                        {isWalkIn ? 'Cliente General' : selectedStudent?.name || 'Sin cliente'}
                    </div>
                </div>

                {/* Selector de Modo: Cobro Inmediato vs Generar Deuda */}
                {onChangeOperationType && (
                    <div className="grid grid-cols-2 gap-1 rounded-xl bg-neutral-200/70 dark:bg-[#141414] p-1">
                        <button
                            type="button"
                            onClick={() => onChangeOperationType('charge')}
                            className={`flex items-center justify-center gap-1.5 rounded-lg py-1.5 text-xs font-semibold transition-all ${
                                !isDebtMode
                                    ? 'bg-white dark:bg-[#262626] text-brand-primary shadow-xs'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900'
                            }`}
                        >
                            <Receipt className="h-3.5 w-3.5" />
                            <span>Cobro Inmediato</span>
                        </button>
                        <button
                            type="button"
                            onClick={() => onChangeOperationType('debt')}
                            disabled={isWalkIn}
                            title={
                                isWalkIn
                                    ? 'Seleccione un estudiante para generar una deuda en cuenta'
                                    : 'Registrar cargo pendiente de pago en la cuenta del estudiante'
                            }
                            className={`flex items-center justify-center gap-1.5 rounded-lg py-1.5 text-xs font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed ${
                                isDebtMode
                                    ? 'bg-amber-600 text-white shadow-xs'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900'
                            }`}
                        >
                            <Clock className="h-3.5 w-3.5" />
                            <span>Generar Deuda</span>
                        </button>
                    </div>
                )}
            </div>

            {/* Contenido con Scroll Interno Controlado */}
            <div className="flex-1 overflow-y-auto px-4 py-3 space-y-4 pr-2">
                {/* Si se está liquidando una deuda existente */}
                {activePendingDebt && !isDebtMode && (
                    <div className="rounded-xl border border-amber-300 dark:border-amber-800/60 bg-amber-50/80 dark:bg-amber-950/30 p-3 flex items-start justify-between gap-2 text-xs">
                        <div>
                            <span className="font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider text-[10px] block">
                                Liquidando Deuda Existente #{activePendingDebt.id}
                            </span>
                            <span className="font-semibold text-neutral-900 dark:text-neutral-100">
                                {activePendingDebt.concept}
                            </span>
                            <span className="block text-[11px] text-neutral-500 dark:text-neutral-400">
                                Vencimiento: {activePendingDebt.due_date}
                            </span>
                        </div>
                        {onClearPendingDebt && (
                            <button
                                type="button"
                                onClick={onClearPendingDebt}
                                className="rounded-md p-1 text-neutral-500 hover:bg-amber-200/50 dark:hover:bg-amber-900/40"
                                title="Cancelar selección de deuda"
                            >
                                <X className="h-3.5 w-3.5" />
                            </button>
                        )}
                    </div>
                )}

                {/* Concepto seleccionado y Cantidad */}
                {selectedConcept ? (
                    <div className="rounded-xl border border-neutral-200 dark:border-[#262626] p-3 bg-neutral-50/50 dark:bg-[#1f1f1f]/50 space-y-3">
                        <div className="flex items-start justify-between gap-2">
                            <div className="min-w-0">
                                <div className="text-xs font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                    {activePendingDebt ? activePendingDebt.concept : selectedConcept.name}
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
                        {!activePendingDebt && (
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
                        )}
                    </div>
                ) : (
                    <div className="rounded-xl border border-dashed border-neutral-200 dark:border-[#262626] p-6 text-center text-xs text-neutral-400">
                        Selecciona un concepto del catálogo para {isDebtMode ? 'generar la deuda' : 'armar el cobro'}
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

                {/* MODO DEUDA: Fecha de Vencimiento y Explicación CxC */}
                {isDebtMode ? (
                    <div className="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 p-3 space-y-2.5">
                        <div className="flex items-center justify-between text-xs">
                            <label className="font-semibold text-amber-900 dark:text-amber-300">
                                Fecha de Vencimiento:
                            </label>
                            <input
                                type="date"
                                value={dueDate}
                                onChange={(e) => onChangeDueDate && onChangeDueDate(e.target.value)}
                                className="rounded-lg border border-amber-300 dark:border-amber-800 bg-white dark:bg-[#171717] px-2.5 py-1 text-xs font-semibold text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            />
                        </div>

                        <div className="flex items-center gap-1.5 text-[11px]">
                            <button
                                type="button"
                                onClick={() => setQuickDueDate(15)}
                                className="rounded-md bg-amber-100 dark:bg-amber-900/40 px-2 py-1 text-amber-800 dark:text-amber-300 hover:bg-amber-200 font-medium"
                            >
                                +15 días
                            </button>
                            <button
                                type="button"
                                onClick={() => setQuickDueDate(30)}
                                className="rounded-md bg-amber-100 dark:bg-amber-900/40 px-2 py-1 text-amber-800 dark:text-amber-300 hover:bg-amber-200 font-medium"
                            >
                                +30 días
                            </button>
                            <button
                                type="button"
                                onClick={() => setQuickDueDate('eom')}
                                className="rounded-md bg-amber-100 dark:bg-amber-900/40 px-2 py-1 text-amber-800 dark:text-amber-300 hover:bg-amber-200 font-medium"
                            >
                                Fin de mes
                            </button>
                        </div>

                        <p className="text-[11px] text-amber-800/80 dark:text-amber-300/80 leading-relaxed pt-1 border-t border-amber-200/60 dark:border-amber-900/40">
                            Este cargo quedará registrado como <strong>Pendiente</strong> en el estado de cuenta del estudiante para pago posterior en Caja o en el Kiosco de Autoservicio.
                        </p>
                    </div>
                ) : (
                    <>
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
                                    {[100, 500, 1000, 2000].map((amt) => (
                                        <button
                                            key={amt}
                                            type="button"
                                            onClick={() => handleAddCash(amt)}
                                            className="rounded-md bg-neutral-200 dark:bg-[#262626] px-2 py-1 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-[#333333]"
                                        >
                                            +{amt.toLocaleString()}
                                        </button>
                                    ))}
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
                    </>
                )}

                {/* Notas adicionales */}
                <div>
                    <input
                        type="text"
                        value={notes}
                        onChange={(e) => onChangeNotes(e.target.value)}
                        placeholder={isDebtMode ? 'Observación o motivo del cargo...' : 'Nota o referencia opcional...'}
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

            {/* Footer Fijo con Total y Acción de Cobro o Deuda */}
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
                        <span>{isDebtMode ? 'Total a Cargar:' : 'Total a Cobrar:'}</span>
                        <span className={`text-lg ${isDebtMode ? 'text-amber-600 dark:text-amber-400' : 'text-brand-primary'}`}>
                            {formatMoney(total)}
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    onClick={onProcessPayment}
                    disabled={!canCheckout}
                    className={`w-full flex items-center justify-center gap-2 rounded-xl text-white py-3 text-sm font-bold shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer ${
                        isDebtMode
                            ? 'bg-amber-600 hover:bg-amber-500'
                            : 'bg-brand-primary hover:bg-brand-dark'
                    }`}
                >
                    {processing ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            <span>{isDebtMode ? 'Generando deuda...' : 'Procesando cobro...'}</span>
                        </>
                    ) : isDebtMode ? (
                        <>
                            <FilePlus2 className="h-4 w-4" />
                            <span>Generar Deuda en Cuenta</span>
                        </>
                    ) : (
                        <>
                            <Check className="h-4 w-4 stroke-[3]" />
                            <span>{activePendingDebt ? 'Liquidar y Cobrar Deuda' : 'Registrar Cobro'}</span>
                        </>
                    )}
                </button>

                <PoweredByBills variant="receipt" />
            </div>
        </div>
    );
}
