import React, { useState, useMemo } from 'react';
import { Head, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { History, Sparkles, AlertCircle } from 'lucide-react';
import StudentPicker from '@/components/pos/StudentPicker';
import ConceptCatalog from '@/components/pos/ConceptCatalog';
import TicketPanel from '@/components/pos/TicketPanel';
import ReceiptModal from '@/components/pos/ReceiptModal';
import ShiftDrawer from '@/components/pos/ShiftDrawer';
import { formatMoney, getCsrfToken } from '@/components/pos/utils';

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
    // ─── ESTADO DEL CLIENTE ──────────────────────────────────────────
    const [selectedStudent, setSelectedStudent] = useState(initialStudent);
    const [isWalkIn, setIsWalkIn] = useState(false);

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

    // ─── MODALES Y DRAWER ────────────────────────────────────────────
    const [activeReceipt, setActiveReceipt] = useState(null);
    const [isReceiptOpen, setIsReceiptOpen] = useState(false);
    const [isShiftOpen, setIsShiftOpen] = useState(false);
    const [shiftPayments, setShiftPayments] = useState(todayPayments);
    const [shiftTotal, setShiftTotal] = useState(todayTotal);

    // Cálculos monetarios del ticket en tiempo real
    const unitPrice = useMemo(() => {
        if (!selectedConcept) return 0;
        if (selectedConcept.is_open) return Number(customAmount) || 0;
        if (!selectedConcept.is_fixed && customAmount > 0) return Number(customAmount);
        return Number(selectedConcept.amount) || 0;
    }, [selectedConcept, customAmount]);

    const subtotal = Math.max(0, unitPrice * quantity);
    const total = Math.max(0, subtotal - (Number(discount) || 0));
    const change = paymentMethod === 'Efectivo' && receivedCash ? Number(receivedCash) - total : 0;

    const handleSelectStudent = (student) => {
        setSelectedStudent(student);
        setIsWalkIn(false);
        setErrorMessage(null);
    };

    const handleToggleWalkIn = (active) => {
        setIsWalkIn(active);
        if (active) setSelectedStudent(null);
        setErrorMessage(null);
    };

    const handleSelectConcept = (concept) => {
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

    const resetForm = () => {
        setSelectedStudent(null);
        setIsWalkIn(false);
        setQuantity(1);
        setCustomName('');
        setCustomAmount(0);
        setDiscount(0);
        setReceivedCash('');
        setNotes('');
        setRnc('');
        setCompanyName('');
        setNcfType('B02');
        setErrorMessage(null);
    };

    // Procesar Cobro contra el endpoint transaccional del servidor
    const handleProcessPayment = async () => {
        setErrorMessage(null);

        if (!selectedStudent && !isWalkIn) {
            setErrorMessage('Por favor seleccione un estudiante o active "Público General".');
            return;
        }

        if (!selectedConcept) {
            setErrorMessage('Seleccione un concepto para cobrar.');
            return;
        }

        if (selectedConcept.is_open && !customName.trim()) {
            setErrorMessage('Debe indicar la descripción del Cobro Diverso.');
            return;
        }

        if (total <= 0) {
            setErrorMessage('El total a cobrar debe ser mayor que RD$ 0.00.');
            return;
        }

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

        setProcessing(true);

        const payload = {
            walk_in: isWalkIn,
            student_id: isWalkIn ? null : selectedStudent?.id,
            payment_concept_id: selectedConcept.id,
            concept_label: selectedConcept.is_open ? customName.trim() : null,
            quantity: Number(quantity),
            unit_amount: (!selectedConcept.is_fixed || selectedConcept.is_open) ? Number(unitPrice) : null,
            discount_amount: Number(discount) || 0,
            gateway: paymentMethod,
            cash_received: paymentMethod === 'Efectivo' ? Number(receivedCash) : null,
            notes: notes.trim() || null,
            ncf_type: ncfType,
            rnc_client: ncfType === 'B01' ? rnc.replace(/\D/g, '') : null,
            company_name: ncfType === 'B01' ? companyName.trim() : null,
        };

        try {
            const res = await fetch('/admin/pos/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (res.status === 201 && data.receipt) {
                // Cobro exitoso: actualizar listado de turno y abrir recibo
                setActiveReceipt(data.receipt);
                setIsReceiptOpen(true);
                setShiftPayments((prev) => [data.receipt, ...prev]);
                setShiftTotal((prev) => prev + Number(data.receipt.total || 0));
                resetForm();
            } else if (res.status === 422 && data.errors) {
                // Errores de validación por campo
                const firstError = Object.values(data.errors).flat()[0] || data.message;
                setErrorMessage(firstError);
            } else {
                setErrorMessage(data.message || 'Error inesperado al registrar el cobro.');
            }
        } catch (err) {
            console.error('Error procesando pago POS:', err);
            setErrorMessage('Error de conexión con el servidor. No se confirmó el cobro; verifique su conexión e intente nuevamente.');
        } finally {
            setProcessing(false);
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Terminal de Caja" />

            <div className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 pb-20 lg:pb-8 space-y-4">
                {/* Barra de Encabezado Superior */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-neutral-200 dark:border-[#262626] pb-3">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                                Terminal de Caja
                            </h1>
                            <span className="rounded-md bg-[#bb3902]/10 text-[#bb3902] px-2 py-0.5 text-xs font-semibold">
                                POS
                            </span>
                        </div>
                        <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Cobro de servicios, emisión de recibos y facturación electrónica institucional
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setIsShiftOpen(true)}
                            className="flex items-center gap-2 rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] hover:bg-neutral-50 dark:hover:bg-[#1f1f1f] px-3.5 py-2 text-xs font-medium text-neutral-700 dark:text-neutral-300 transition-colors shadow-xs"
                        >
                            <History className="h-4 w-4 text-[#bb3902]" />
                            <span>Turno ({shiftPayments.length})</span>
                            <span className="font-bold text-neutral-900 dark:text-neutral-100">
                                {formatMoney(shiftTotal)}
                            </span>
                        </button>
                    </div>
                </div>

                {/* Cuadrícula Principal: Catálogo (Izquierda) + Ticket (Derecha) */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                    {/* Columna Izquierda: Buscador de Estudiante + Catálogo */}
                    <div className="lg:col-span-7 xl:col-span-8 space-y-4">
                        {/* Selector de Estudiante */}
                        <div className="rounded-2xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-4 shadow-xs">
                            <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                                Cliente / Estudiante
                            </label>
                            <StudentPicker
                                selectedStudent={selectedStudent}
                                onSelectStudent={handleSelectStudent}
                                isWalkIn={isWalkIn}
                                onToggleWalkIn={handleToggleWalkIn}
                                walkInAvailable={walkInAvailable}
                            />
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
                            selectedConcept={selectedConcept}
                            quantity={quantity}
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
