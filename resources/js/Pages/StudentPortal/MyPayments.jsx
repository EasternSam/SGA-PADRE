import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    CreditCard,
    ArrowLeft,
    CheckCircle2,
    Clock,
    AlertCircle,
    Receipt,
    Download,
    DollarSign,
    ShieldCheck,
    X,
    Lock,
    Printer
} from 'lucide-react';

export default function MyPayments({
    auth,
    student,
    pendingPayments = [],
    paymentHistory = [],
    totalPending = 0
}) {
    // Payment Modal
    const [selectedPayment, setSelectedPayment] = useState(null);
    const [isCheckoutOpen, setIsCheckoutOpen] = useState(false);
    const [cardData, setCardData] = useState({
        card_name: student?.name || '',
        card_number: '•••• •••• •••• 4242',
        exp_date: '12/28',
        cvv: '123',
    });
    const [processing, setProcessing] = useState(false);

    const formatCurrency = (val) => {
        return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(val || 0);
    };

    const openCheckout = (payment) => {
        setSelectedPayment(payment);
        setIsCheckoutOpen(true);
    };

    const handleProcessPayment = (e) => {
        e.preventDefault();
        setProcessing(true);

        router.post(route('student.payments.pay'), {
            payment_id: selectedPayment.id,
            card_name: cardData.card_name,
            card_number: cardData.card_number,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setIsCheckoutOpen(false);
                setProcessing(false);
            },
            onError: () => setProcessing(false)
        });
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Mis Pagos & Finanzas" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            href={route('student.dashboard')}
                            className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Volver al Portal
                        </Link>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Mis Pagos & Estado de Cuenta
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                            <ShieldCheck className="h-4 w-4 text-emerald-500" />
                            Pasarela Segura SSL 256-bit
                        </span>
                    </div>
                </div>

                {/* Summary Banner */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Balance Total Pendiente
                        </span>
                        <div className="mt-2 flex items-baseline gap-2">
                            <span className={`text-3xl font-black ${
                                totalPending > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'
                            }`}>
                                {formatCurrency(totalPending)}
                            </span>
                        </div>
                        <p className="mt-1 text-[11px] text-slate-400">
                            {totalPending > 0 ? `${pendingPayments.length} cuotas pendientes por saldar` : 'Al día con la tesorería'}
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Transacciones Realizadas
                        </span>
                        <div className="mt-2 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-slate-900 dark:text-white">
                                {paymentHistory.length}
                            </span>
                            <span className="text-xs font-medium text-slate-500">recibos</span>
                        </div>
                        <p className="mt-1 text-[11px] text-slate-400">Histórico de pagos aprobados</p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Métodos Aceptados
                        </span>
                        <div className="mt-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span className="rounded bg-slate-100 px-2 py-1 dark:bg-slate-800">Visa</span>
                            <span className="rounded bg-slate-100 px-2 py-1 dark:bg-slate-800">Mastercard</span>
                            <span className="rounded bg-slate-100 px-2 py-1 dark:bg-slate-800">CardNET</span>
                            <span className="rounded bg-slate-100 px-2 py-1 dark:bg-slate-800">Azul</span>
                        </div>
                        <p className="mt-2 text-[10px] text-slate-400">Procesamiento instantáneo en línea</p>
                    </div>
                </div>

                {/* Section 1: Pending Invoices */}
                <div className="space-y-3">
                    <h2 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <AlertCircle className="h-4 w-4 text-rose-500" />
                        Cuotas & Cargos Pendientes
                    </h2>

                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                    <tr>
                                        <th className="px-5 py-3.5">Concepto</th>
                                        <th className="px-5 py-3.5">Fecha de Vencimiento</th>
                                        <th className="px-5 py-3.5 text-right">Monto Base</th>
                                        <th className="px-5 py-3.5 text-right">Mora / Recargo</th>
                                        <th className="px-5 py-3.5 text-right">Total a Pagar</th>
                                        <th className="px-5 py-3.5 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {pendingPayments.map(p => (
                                        <tr key={p.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                            <td className="px-5 py-3.5 font-bold text-slate-900 dark:text-white">
                                                {p.concept}
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                                <span className={p.is_overdue ? 'text-rose-600 font-bold' : ''}>
                                                    {p.due_date} {p.is_overdue && '(Vencida)'}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                                                {formatCurrency(p.amount)}
                                            </td>
                                            <td className="px-5 py-3.5 text-right font-mono text-xs text-slate-500">
                                                {p.late_fee > 0 ? formatCurrency(p.late_fee) : 'RD$ 0.00'}
                                            </td>
                                            <td className="px-5 py-3.5 text-right font-mono text-sm font-bold text-slate-900 dark:text-white">
                                                {formatCurrency(p.total)}
                                            </td>
                                            <td className="px-5 py-3.5 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => openCheckout(p)}
                                                    className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition"
                                                >
                                                    <CreditCard className="h-3.5 w-3.5" />
                                                    Pagar en Línea
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {pendingPayments.length === 0 && (
                                        <tr>
                                            <td colSpan={6} className="py-8 text-center text-xs font-semibold text-emerald-600">
                                                ¡Excelente! No tienes cuotas ni pagos pendientes en este momento.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {/* Section 2: Payment History */}
                <div className="space-y-3">
                    <h2 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Receipt className="h-4 w-4 text-brand-primary dark:text-blue-400" />
                        Histórico de Recibos & Comprobantes Fiscales
                    </h2>

                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                    <tr>
                                        <th className="px-5 py-3.5">Recibo #</th>
                                        <th className="px-5 py-3.5">NCF Fiscal</th>
                                        <th className="px-5 py-3.5">Concepto</th>
                                        <th className="px-5 py-3.5">Método de Pago</th>
                                        <th className="px-5 py-3.5">Fecha</th>
                                        <th className="px-5 py-3.5 text-right">Monto Pagado</th>
                                        <th className="px-5 py-3.5 text-center">Recibo</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {paymentHistory.map(h => (
                                        <tr key={h.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                            <td className="px-5 py-3.5 font-mono text-xs font-bold text-slate-900 dark:text-white">
                                                {h.receipt_number}
                                            </td>
                                            <td className="px-5 py-3.5 font-mono text-xs text-slate-500">
                                                {h.ncf}
                                            </td>
                                            <td className="px-5 py-3.5 text-xs font-medium text-slate-800 dark:text-slate-200">
                                                {h.concept}
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-slate-500">
                                                {h.payment_method}
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-slate-500">
                                                {h.paid_at}
                                            </td>
                                            <td className="px-5 py-3.5 text-right font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                                {formatCurrency(h.amount)}
                                            </td>
                                            <td className="px-5 py-3.5 text-center">
                                                <button
                                                    type="button"
                                                    onClick={() => window.print()}
                                                    className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                                                >
                                                    <Printer className="h-3.5 w-3.5" />
                                                    Imprimir
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                    {paymentHistory.length === 0 && (
                                        <tr>
                                            <td colSpan={7} className="py-8 text-center text-xs text-slate-400">
                                                No se registran transacciones pasadas en el sistema.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {/* Checkout Modal */}
                {isCheckoutOpen && selectedPayment && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                                <div className="flex items-center gap-2">
                                    <Lock className="h-4 w-4 text-emerald-600" />
                                    <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                        Pasarela de Pago Seguro
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsCheckoutOpen(false)}
                                    className="rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <form onSubmit={handleProcessPayment} className="mt-4 space-y-4">
                                <div className="rounded-xl bg-slate-50 p-3.5 text-center dark:bg-slate-800/60">
                                    <span className="text-xs text-slate-500 font-medium">Concepto: {selectedPayment.concept}</span>
                                    <div className="mt-1 font-mono text-2xl font-black text-slate-900 dark:text-white">
                                        {formatCurrency(selectedPayment.total)}
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Titular de la Tarjeta *
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={cardData.card_name}
                                        onChange={(e) => setCardData({ ...cardData, card_name: e.target.value })}
                                        placeholder="Nombre tal como aparece en la tarjeta"
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Número de Tarjeta *
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={cardData.card_number}
                                        onChange={(e) => setCardData({ ...cardData, card_number: e.target.value })}
                                        placeholder="4000 1234 5678 9010"
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Expiración (MM/AA) *
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={cardData.exp_date}
                                            onChange={(e) => setCardData({ ...cardData, exp_date: e.target.value })}
                                            placeholder="12/28"
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono text-center text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            CVV / CVC *
                                        </label>
                                        <input
                                            type="password"
                                            required
                                            maxLength={4}
                                            value={cardData.cvv}
                                            onChange={(e) => setCardData({ ...cardData, cvv: e.target.value })}
                                            placeholder="123"
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono text-center text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                </div>

                                <div className="flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsCheckoutOpen(false)}
                                        className="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-emerald-700 disabled:opacity-50"
                                    >
                                        {processing ? 'Procesando Transacción...' : `Pagar ${formatCurrency(selectedPayment.total)}`}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>, document.body)
                )}
            </div>
        </AuthenticatedLayout>
    );
}
