import React, { useState, useMemo } from 'react';
import { Head, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { History, Clock, CheckCircle2, FilePlus2, ArrowRight } from 'lucide-react';
import StudentPicker from '@/components/pos/StudentPicker';
import ConceptCatalog from '@/components/pos/ConceptCatalog';
import TicketPanel from '@/components/pos/TicketPanel';
import ReceiptModal from '@/components/pos/ReceiptModal';
import ShiftDrawer from '@/components/pos/ShiftDrawer';
import PoweredByBills from '@/components/bills/PoweredByBills';
import { formatMoney, getCsrfHeaders } from '@/components/pos/utils';

function defaultDueDateString() {
    const d = new Date();
    d.setDate(d.getDate() + 30);
    return d.toISOString().split('T')[0];
}

export default function PosIndex({
    concepts = [],
    walkInAvailable = true,
    initialStudent = null,
    todayPayments = [],
    todayTotal = 0,
    issuer = {},
    gateways = ['Efectivo', 'Tarjeta', 'Transferencia'],
    maxQuantity = 99,
}) {
    const { props } = usePage();
    // ─── ESTADO DEL CLIENTE ──────────────────────────────────────────
    const [selectedStudent, setSelectedStudent] = useState(initialStudent);
    const [isWalkIn, setIsWalkIn] = useState(false);

    // ─── MODO DE OPERACIÓN (COBRO INMEDIATO VS GENERAR DEUDA) ────────
    const [operationType, setOperationType] = useState('charge'); // 'charge' | 'debt'
    const [dueDate, setDueDate] = useState(defaultDueDateString);
    const [activePendingDebt, setActivePendingDebt] = useState(null);

    // ─── ESTADO DEL TICKET & CONCEPTO ────────────────────────────────
    const [selectedConcept, setSelectedConcept] = useState(concepts[0] || null);
    const [quantity, setQuantity] = useState(1);
    const [customName, setCustomName] = useState('');
    const [customAmount, setCustomAmount] = useState(0);
    const [discount, setDiscount] = useState(0);
    const [notes, setNotes] = useState('');

    // ─── MÉTODO DE PAGO & CAJA ──────────────────────────────────────
    const [paymentMethod, setPaymentMethod] = useState(gateways[0] || 'Efectivo');
    const [receivedCash, setReceivedCash] = useState('');

    // ─── FACTURACIÓN FISCAL (DGII) ───────────────────────────────────
    const [ncfType, setNcfType] = useState('B02');
    const [rnc, setRnc] = useState('');
    const [companyName, setCompanyName] = useState('');

    // ─── ESTADO DE ENVÍO Y RETROALIMENTACIÓN ─────────────────────────
    const [processing, setProcessing] = useState(false);
    const [errorMessage, setErrorMessage] = useState(null);
    const [successBanner, setSuccessBanner] = useState(null);

    // ─── MODALES Y DRAWER ────────────────────────────────────────────
    const [activeReceipt, setActiveReceipt] = useState(null);
    const [isReceiptOpen, setIsReceiptOpen] = useState(false);
    const [isShiftOpen, setIsShiftOpen] = useState(false);
    const [shiftPayments, setShiftPayments] = useState(todayPayments);
    const [shiftTotal, setShiftTotal] = useState(todayTotal);

    // Cálculos monetarios del ticket en tiempo real
    const unitPrice = useMemo(() => {
        if (activePendingDebt) {
            return Number(activePendingDebt.original_amount || activePendingDebt.amount) || 0;
        }
        if (!selectedConcept) return 0;
        if (selectedConcept.is_open) return Number(customAmount) || 0;
        if (!selectedConcept.is_fixed && customAmount > 0) return Number(customAmount);
        return Number(selectedConcept.amount) || 0;
    }, [selectedConcept, customAmount, activePendingDebt]);

    const effectiveQty = activePendingDebt ? 1 : quantity;
    const subtotal = Math.max(0, unitPrice * effectiveQty);
    const total = Math.max(0, subtotal - (Number(discount) || 0));
    const change = paymentMethod === 'Efectivo' && receivedCash ? Number(receivedCash) - total : 0;

    const handleSelectStudent = (student) => {
        setSelectedStudent(student);
        setIsWalkIn(false);
        setActivePendingDebt(null);
        setErrorMessage(null);
        setSuccessBanner(null);
    };

    const handleToggleWalkIn = (active) => {
        setIsWalkIn(active);
        if (active) {
            setSelectedStudent(null);
            setActivePendingDebt(null);
            setOperationType('charge');
        }
        setErrorMessage(null);
        setSuccessBanner(null);
    };

    const handleSelectConcept = (concept) => {
        setActivePendingDebt(null);
        setSelectedConcept(concept);
        setQuantity(1);
        if (concept.is_open) {
            setCustomName('');
            setCustomAmount(0);
        } else {
            setCustomAmount(concept.amount);
        }
        setErrorMessage(null);
    };

    const handleSelectPendingDebtToPay = (debt) => {
        setOperationType('charge');
        setActivePendingDebt(debt);
        const matchingConcept = concepts.find((c) => c.id === debt.payment_concept_id) || concepts[0] || null;
        setSelectedConcept(matchingConcept);
        setQuantity(debt.quantity || 1);
        setCustomName(debt.concept_label || debt.concept || '');
        setCustomAmount(debt.original_amount || debt.amount || 0);
        setDiscount(debt.discount_amount || 0);
        setNotes(debt.notes || '');
        setErrorMessage(null);
        setSuccessBanner(null);
    };

    const resetTicketOnly = () => {
        setActivePendingDebt(null);
        setQuantity(1);
        setCustomName('');
        setCustomAmount(selectedConcept && !selectedConcept.is_open ? selectedConcept.amount : 0);
        setDiscount(0);
        setReceivedCash('');
        setNotes('');
        setRnc('');
        setCompanyName('');
        setNcfType('B02');
        setErrorMessage(null);
    };

    // Procesar Cobro o Generar Deuda contra el endpoint transaccional del servidor
    const handleProcessPayment = async () => {
        setErrorMessage(null);
        setSuccessBanner(null);

        const isDebtMode = operationType === 'debt';

        if (!selectedStudent && !isWalkIn) {
            setErrorMessage('Por favor seleccione un estudiante o active "Público General".');
            return;
        }

        if (isDebtMode && isWalkIn) {
            setErrorMessage('Para generar una deuda en cuenta debe seleccionar un estudiante registrado.');
            return;
        }

        if (!selectedConcept) {
            setErrorMessage('Seleccione un concepto del catálogo.');
            return;
        }

        if (selectedConcept.is_open && !customName.trim() && !activePendingDebt) {
            setErrorMessage('Debe indicar la descripción del Cobro Diverso.');
            return;
        }

        if (total <= 0) {
            setErrorMessage('El monto total debe ser mayor que RD$ 0.00.');
            return;
        }

        if (!isDebtMode) {
            if (paymentMethod === 'Efectivo') {
                const cash = Number(receivedCash) || 0;
                if (cash < total) {
                    setErrorMessage(`El efectivo recibido (${formatMoney(cash)}) no cubre el total (${formatMoney(total)}).`);
                    return;
                }
            }

            if (ncfType === 'B01') {
                const cleanRnc = rnc.replace(/\D/g, '');
                if (cleanRnc.length !== 9 && cleanRnc.length !== 11) {
                    setErrorMessage('El crédito fiscal (B01) requiere un RNC válido (9 dígitos) o cédula (11 dígitos).');
                    return;
                }
            }
        }

        setProcessing(true);

        const payload = {
            operation_type: operationType,
            pending_payment_id: !isDebtMode && activePendingDebt ? activePendingDebt.id : null,
            due_date: isDebtMode ? dueDate : null,
            walk_in: isWalkIn,
            student_id: isWalkIn ? null : selectedStudent?.id,
            payment_concept_id: selectedConcept.id,
            concept_label: selectedConcept.is_open ? customName.trim() : (activePendingDebt?.concept_label || null),
            quantity: Number(effectiveQty),
            unit_amount: (!selectedConcept.is_fixed || selectedConcept.is_open) ? Number(unitPrice) : null,
            discount_amount: Number(discount) || 0,
            gateway: isDebtMode ? null : paymentMethod,
            cash_received: !isDebtMode && paymentMethod === 'Efectivo' ? Number(receivedCash) : null,
            notes: notes.trim() || null,
            ncf_type: isDebtMode ? null : ncfType,
            rnc_client: !isDebtMode && ncfType === 'B01' ? rnc.replace(/\D/g, '') : null,
            company_name: !isDebtMode && ncfType === 'B01' ? companyName.trim() : null,
        };

        try {
            const res = await fetch('/admin/pos/process', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...getCsrfHeaders(props?.csrf_token),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (res.status === 201 && data.receipt) {
                if (data.student) {
                    setSelectedStudent(data.student);
                }

                if (isDebtMode || data.receipt.is_debt) {
                    setSuccessBanner(
                        `Deuda registrada: ${data.receipt.concept} por ${formatMoney(data.receipt.total)} (Vence: ${data.receipt.due_date || dueDate}).`
                    );
                    resetTicketOnly();
                    setOperationType('charge');
                } else {
                    // Cobro exitoso: actualizar listado de turno y abrir recibo
                    setActiveReceipt(data.receipt);
                    setIsReceiptOpen(true);
                    setShiftPayments((prev) => [data.receipt, ...prev]);
                    setShiftTotal((prev) => prev + Number(data.receipt.total || 0));
                    resetTicketOnly();
                }
            } else if (res.status === 422 && data.errors) {
                const firstError = Object.values(data.errors).flat()[0] || data.message;
                setErrorMessage(firstError);
            } else {
                setErrorMessage(data.message || 'Error inesperado al procesar la operación.');
            }
        } catch (err) {
            console.error('Error procesando operación POS:', err);
            setErrorMessage('Error de conexión con el servidor. Verifique su conexión e intente nuevamente.');
        } finally {
            setProcessing(false);
        }
    };

    const pendingList = selectedStudent?.pending_payments || [];

    return (
        <AuthenticatedLayout title="Terminal de Caja">
            <Head title="Terminal de Caja" />

            <div className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 pb-20 lg:pb-8 space-y-4">
                {/* Barra de Encabezado Superior */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-neutral-200 dark:border-[#262626] pb-3">
                    <div>
                        <div className="flex flex-wrap items-center gap-2.5">
                            <h1 className="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                                Terminal de Caja
                            </h1>
                            <span className="rounded-md bg-brand-primary/10 text-brand-primary px-2 py-0.5 text-xs font-semibold">
                                POS & Estado de Cuenta
                            </span>
                            <PoweredByBills variant="badge" />
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setIsShiftOpen(true)}
                            className="flex items-center gap-2 rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] hover:bg-neutral-50 dark:hover:bg-[#1f1f1f] px-3.5 py-2 text-xs font-medium text-neutral-700 dark:text-neutral-300 transition-colors shadow-xs"
                        >
                            <History className="h-4 w-4 text-brand-primary" />
                            <span>Turno ({shiftPayments.length})</span>
                            <span className="font-bold text-neutral-900 dark:text-neutral-100">
                                {formatMoney(shiftTotal)}
                            </span>
                        </button>
                    </div>
                </div>

                {/* Banner de confirmación al generar una deuda */}
                {successBanner && (
                    <div className="rounded-2xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3 text-xs font-semibold text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                            <CheckCircle2 className="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                            <span>{successBanner}</span>
                        </div>
                        <button
                            type="button"
                            onClick={() => setSuccessBanner(null)}
                            className="text-emerald-700 dark:text-emerald-300 hover:underline text-xs"
                        >
                            Cerrar
                        </button>
                    </div>
                )}

                {/* Cuadrícula Principal: Catálogo (Izquierda) + Ticket (Derecha) */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                    {/* Columna Izquierda: Buscador de Estudiante + Deudas Pendientes + Catálogo */}
                    <div className="lg:col-span-7 xl:col-span-8 space-y-4">
                        {/* Selector de Estudiante */}
                        <div className="rounded-2xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-4 shadow-xs space-y-3">
                            <div className="flex items-center justify-between">
                                <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                                    Cliente / Estudiante
                                </label>
                                {selectedStudent && !isWalkIn && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setActivePendingDebt(null);
                                            setOperationType(operationType === 'debt' ? 'charge' : 'debt');
                                        }}
                                        className={`inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors ${
                                            operationType === 'debt'
                                                ? 'bg-amber-600 text-white'
                                                : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-100'
                                        }`}
                                    >
                                        <FilePlus2 className="h-3.5 w-3.5" />
                                        <span>
                                            {operationType === 'debt' ? 'Modo Deuda Activo' : '+ Generar Deuda al Estudiante'}
                                        </span>
                                    </button>
                                )}
                            </div>

                            <StudentPicker
                                selectedStudent={selectedStudent}
                                onSelectStudent={handleSelectStudent}
                                isWalkIn={isWalkIn}
                                onToggleWalkIn={handleToggleWalkIn}
                                walkInAvailable={walkInAvailable}
                            />

                            {/* Panel de Deudas / Cargos Pendientes del Estudiante Seleccionado */}
                            {selectedStudent && !isWalkIn && (
                                <div className="pt-2 border-t border-neutral-100 dark:border-[#262626]">
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="flex items-center gap-2 text-xs">
                                            <Clock className="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                            <span className="font-semibold text-neutral-800 dark:text-neutral-200">
                                                Deudas / Cargos Pendientes ({pendingList.length})
                                            </span>
                                        </div>
                                        <span className="text-xs font-bold text-amber-700 dark:text-amber-400">
                                            Balance Pendiente: {formatMoney(selectedStudent.balance || 0)}
                                        </span>
                                    </div>

                                    {pendingList.length === 0 ? (
                                        <div className="rounded-xl bg-neutral-50 dark:bg-[#141414] px-3 py-2 text-xs text-neutral-500 dark:text-neutral-400 flex items-center justify-between">
                                            <span>El estudiante no tiene cargos pendientes de pago.</span>
                                            <button
                                                type="button"
                                                onClick={() => setOperationType('debt')}
                                                className="text-amber-600 dark:text-amber-400 font-semibold hover:underline"
                                            >
                                                Asignar un cargo
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                            {pendingList.map((debt) => {
                                                const isLoaded = activePendingDebt?.id === debt.id;
                                                return (
                                                    <div
                                                        key={debt.id}
                                                        className={`flex items-center justify-between gap-3 rounded-xl border px-3 py-2 text-xs transition-all ${
                                                            isLoaded
                                                                ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30'
                                                                : 'border-neutral-200 dark:border-[#262626] bg-neutral-50/60 dark:bg-[#141414]'
                                                        }`}
                                                    >
                                                        <div className="min-w-0">
                                                            <div className="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                                                {debt.concept}
                                                            </div>
                                                            <div className="text-[11px] text-neutral-500 dark:text-neutral-400">
                                                                Vence: {debt.due_date} • #{debt.id}
                                                            </div>
                                                        </div>
                                                        <div className="flex items-center gap-2.5 shrink-0">
                                                            <span className="font-bold text-neutral-900 dark:text-neutral-100">
                                                                {formatMoney(debt.amount)}
                                                            </span>
                                                            <button
                                                                type="button"
                                                                onClick={() => handleSelectPendingDebtToPay(debt)}
                                                                className={`inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors ${
                                                                    isLoaded
                                                                        ? 'bg-amber-600 text-white'
                                                                        : 'bg-brand-primary/10 text-brand-primary hover:bg-brand-primary hover:text-white'
                                                                }`}
                                                            >
                                                                <span>{isLoaded ? 'En Ticket' : 'Cobrar'}</span>
                                                                <ArrowRight className="h-3 w-3" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Catálogo de Conceptos */}
                        <div className="rounded-2xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-4 shadow-xs min-h-[460px]">
                            <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                                Catálogo de Servicios y Conceptos
                            </label>
                            <ConceptCatalog
                                concepts={concepts}
                                selectedConcept={selectedConcept}
                                onSelectConcept={handleSelectConcept}
                                customName={customName}
                                onChangeCustomName={setCustomName}
                                customAmount={customAmount}
                                onChangeCustomAmount={setCustomAmount}
                            />
                        </div>
                    </div>

                    {/* Columna Derecha: Ticket Panel Pinned */}
                    <div className="lg:col-span-5 xl:col-span-4 sticky top-4">
                        <TicketPanel
                            operationType={operationType}
                            onChangeOperationType={(mode) => {
                                setOperationType(mode);
                                if (mode === 'debt') {
                                    setActivePendingDebt(null);
                                }
                            }}
                            dueDate={dueDate}
                            onChangeDueDate={setDueDate}
                            activePendingDebt={activePendingDebt}
                            onClearPendingDebt={() => setActivePendingDebt(null)}
                            selectedConcept={selectedConcept}
                            quantity={effectiveQty}
                            onChangeQuantity={setQuantity}
                            maxQuantity={maxQuantity}
                            unitPrice={unitPrice}
                            subtotal={subtotal}
                            discount={discount}
                            onChangeDiscount={setDiscount}
                            total={total}
                            paymentMethod={paymentMethod}
                            onChangePaymentMethod={setPaymentMethod}
                            gateways={gateways}
                            receivedCash={receivedCash}
                            onChangeReceivedCash={setReceivedCash}
                            change={change}
                            ncfType={ncfType}
                            onChangeNcfType={setNcfType}
                            rnc={rnc}
                            onChangeRnc={setRnc}
                            companyName={companyName}
                            onChangeCompanyName={setCompanyName}
                            notes={notes}
                            onChangeNotes={setNotes}
                            onProcessPayment={handleProcessPayment}
                            processing={processing}
                            error={errorMessage}
                            selectedStudent={selectedStudent}
                            isWalkIn={isWalkIn}
                        />
                    </div>
                </div>
            </div>

            {/* Modal de Recibo Térmico */}
            <ReceiptModal
                isOpen={isReceiptOpen}
                onClose={() => setIsReceiptOpen(false)}
                receipt={activeReceipt}
                issuer={issuer}
            />

            {/* Drawer de Movimientos y Turno */}
            <ShiftDrawer
                isOpen={isShiftOpen}
                onClose={() => setIsShiftOpen(false)}
                todayPayments={shiftPayments}
                todayTotal={shiftTotal}
                onSelectReceipt={(p) => {
                    setActiveReceipt(p);
                    setIsReceiptOpen(true);
                }}
            />
        </AuthenticatedLayout>
    );
}
