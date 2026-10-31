import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    CreditCard,
    Plus,
    DollarSign,
    Calendar,
    Users,
    CheckCircle2,
    Clock,
    AlertCircle,
    Eye,
    Trash2,
    ArrowRight,
    X,
    Building2,
    ShieldCheck,
    Receipt,
    FileSpreadsheet,
    Sparkles
} from 'lucide-react';

export default function PayrollIndex({
    auth,
    payrolls,
    stats = {},
    defaultDates = {}
}) {
    // Generate Modal state
    const [isGenerateModalOpen, setIsGenerateModalOpen] = useState(false);
    const [generateForm, setGenerateForm] = useState({
        name: defaultDates.name || '',
        start_date: defaultDates.start_date || '',
        end_date: defaultDates.end_date || '',
        cycle_type: 'Mensual',
    });
    const [generating, setGenerating] = useState(false);

    // Detail Modal state
    const [selectedPayroll, setSelectedPayroll] = useState(null);
    const [payrollItems, setPayrollItems] = useState([]);
    const [loadingItems, setLoadingItems] = useState(false);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);

    const formatCurrency = (val) => {
        return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(val || 0);
    };

    const handleGenerate = (e) => {
        e.preventDefault();
        setGenerating(true);
        router.post(route('admin.hr.payroll.generate'), generateForm, {
            preserveScroll: true,
            onSuccess: () => {
                setIsGenerateModalOpen(false);
                setGenerating(false);
            },
            onError: () => setGenerating(false),
        });
    };

    const handleApproveAndPay = (payroll) => {
        if (!confirm(`¿Está seguro de que desea aprobar y dispersar la nómina "${payroll.name}" por un monto total de ${formatCurrency(payroll.total_amount)}? Esto registrará automáticamente el gasto y asiento contable en Tesorería.`)) {
            return;
        }

        router.post(route('admin.hr.payroll.pay', payroll.id), {}, { preserveScroll: true });
    };

    const handleDelete = (payroll) => {
        if (!confirm(`¿Desea eliminar el borrador de nómina "${payroll.name}"?`)) return;
        router.delete(route('admin.hr.payroll.destroy', payroll.id), { preserveScroll: true });
    };

    const openDetails = async (payroll) => {
        setSelectedPayroll(payroll);
        setIsDetailModalOpen(true);
        setLoadingItems(true);

        try {
            const res = await fetch(route('admin.hr.payroll.items', payroll.id));
            const data = await res.json();
            setPayrollItems(data.items || []);
        } catch (err) {
            console.error('Error fetching payroll items:', err);
        } finally {
            setLoadingItems(false);
        }
    };

    return (
        <AuthenticatedLayout auth={auth} title="Nómina de Pago">
            <Head title="Gestión de Nómina & Dispersión" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="inline-flex items-center gap-1.5 rounded-md bg-brand-primary/10 px-2.5 py-1 text-xs font-semibold text-brand-primary dark:bg-blue-900/30 dark:text-blue-300">
                                <Receipt className="h-3.5 w-3.5" />
                                Sistema de Compensación Laboral
                            </span>
                        </div>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Gestión de Nómina Institucional
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <Link
                            href={route('admin.hr.employees')}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Users className="h-3.5 w-3.5 text-slate-500" />
                            Directorio de Personal
                        </Link>

                        <button
                            type="button"
                            onClick={() => setIsGenerateModalOpen(true)}
                            className="inline-flex items-center gap-2 rounded-lg bg-brand-primary px-4 py-2 text-xs font-bold text-white shadow-md hover:bg-[#082260] active:scale-95 transition"
                        >
                            <Plus className="h-4 w-4" />
                            Generar Nueva Nómina
                        </button>
                    </div>
                </div>

                {/* Top Metrics Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Total Pagado en el Año</span>
                            <DollarSign className="h-4 w-4 text-emerald-500" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-2xl font-black text-slate-900 dark:text-white">
                                {formatCurrency(stats.total_paid_ytd)}
                            </span>
                        </div>
                        <p className="mt-1 text-[11px] text-slate-500">
                            Liquidaciones cerradas e integradas a contabilidad
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Nóminas en Borrador</span>
                            <Clock className="h-4 w-4 text-amber-500" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-amber-600 dark:text-amber-400">
                                {stats.pending_drafts || 0}
                            </span>
                            <span className="text-xs font-medium text-slate-500">lotes pendientes de pago</span>
                        </div>
                        <p className="mt-1 text-[11px] text-slate-500">
                            Requieren validación y aprobación final
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Empleados en Nómina</span>
                            <Users className="h-4 w-4 text-brand-primary" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-slate-900 dark:text-white">
                                {stats.active_employees || 0}
                            </span>
                            <span className="text-xs font-medium text-slate-500">colaboradores activos</span>
                        </div>
                        <p className="mt-1 text-[11px] text-slate-500">
                            Elegibles para dispersión automática
                        </p>
                    </div>
                </div>

                {/* Payroll Lots Table */}
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 className="text-base font-bold text-slate-900 dark:text-white">
                            Histórico de Lotes de Nómina
                        </h3>
                        <span className="rounded bg-slate-200/80 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {payrolls.data?.length || 0} lotes
                        </span>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                <tr>
                                    <th className="px-5 py-3.5">Lote / Denominación</th>
                                    <th className="px-5 py-3.5">Período de Nómina</th>
                                    <th className="px-5 py-3.5 text-center">Colaboradores</th>
                                    <th className="px-5 py-3.5 text-right">Monto Total</th>
                                    <th className="px-5 py-3.5 text-center">Estado</th>
                                    <th className="px-5 py-3.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {payrolls.data?.map(payroll => (
                                    <tr key={payroll.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                        <td className="px-5 py-3.5">
                                            <div className="font-bold text-slate-900 dark:text-white">
                                                {payroll.name}
                                            </div>
                                            <div className="text-xs text-slate-400">
                                                Generada el {payroll.created_at}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                            {payroll.start_date} &rarr; {payroll.end_date}
                                        </td>
                                        <td className="px-5 py-3.5 text-center">
                                            <span className="inline-flex items-center gap-1 rounded bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                <Users className="h-3 w-3" />
                                                {payroll.items_count} pers.
                                            </span>
                                        </td>
                                        <td className="px-5 py-3.5 text-right font-mono text-sm font-bold text-slate-900 dark:text-white">
                                            {formatCurrency(payroll.total_amount)}
                                        </td>
                                        <td className="px-5 py-3.5 text-center">
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                                payroll.status === 'Pagado'
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                    : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                                            }`}>
                                                {payroll.status === 'Pagado' ? 'Pagado / Dispersado' : 'Borrador'}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3.5 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => openDetails(payroll)}
                                                    className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                                >
                                                    <Eye className="h-3.5 w-3.5" />
                                                    Desglose
                                                </button>

                                                {payroll.status === 'Borrador' && (
                                                    <>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleApproveAndPay(payroll)}
                                                            className="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700"
                                                        >
                                                            <CheckCircle2 className="h-3.5 w-3.5" />
                                                            Pagar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleDelete(payroll)}
                                                            className="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {payrolls.data?.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="py-12 text-center text-sm text-slate-500">
                                            No hay lotes de nómina generados aún. Haz clic en "Generar Nueva Nómina" para comenzar.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Generate Modal */}
                {isGenerateModalOpen && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Generar Lote de Nómina Automatizada
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setIsGenerateModalOpen(false)}
                                    className="rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <form onSubmit={handleGenerate} className="mt-4 space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Nombre / Identificador del Lote *
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={generateForm.name}
                                        onChange={(e) => setGenerateForm({ ...generateForm, name: e.target.value })}
                                        placeholder="Ej. Nómina Septiembre 2026"
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Fecha Inicio *
                                        </label>
                                        <input
                                            type="date"
                                            required
                                            value={generateForm.start_date}
                                            onChange={(e) => setGenerateForm({ ...generateForm, start_date: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Fecha Fin *
                                        </label>
                                        <input
                                            type="date"
                                            required
                                            value={generateForm.end_date}
                                            onChange={(e) => setGenerateForm({ ...generateForm, end_date: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Ciclo de Pago
                                    </label>
                                    <select
                                        value={generateForm.cycle_type}
                                        onChange={(e) => setGenerateForm({ ...generateForm, cycle_type: e.target.value })}
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="Mensual">Mensual (100% Salario Base)</option>
                                        <option value="Quincenal">Quincenal (50% Salario Base)</option>
                                    </select>
                                </div>

                                <div className="rounded-xl border border-blue-200 bg-blue-50/70 p-3.5 text-xs text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300 space-y-1">
                                    <span className="font-bold flex items-center gap-1">
                                        <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                                        Cálculo Dominicano Automatizado:
                                    </span>
                                    <p className="text-[11px] text-blue-800 dark:text-blue-300">
                                        Se aplicará la retención legal TSS del 5.91% (SFS 3.04% + AFP 2.87%) a todos los colaboradores activos. Los empleados por hora liquidarán según los marcajes del reloj biométrico.
                                    </p>
                                </div>

                                <div className="flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsGenerateModalOpen(false)}
                                        className="rounded-lg px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={generating}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-brand-primary px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-[#082260] disabled:opacity-50"
                                    >
                                        {generating ? 'Calculando Nómina...' : 'Generar Nómina'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>, document.body)
                )}

                {/* Detail Items Modal */}
                {isDetailModalOpen && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 max-h-[90vh] flex flex-col">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white">
                                        Desglose Individual de Nómina: {selectedPayroll?.name}
                                    </h3>
                                    <p className="text-xs text-slate-500">
                                        Período {selectedPayroll?.period || `${selectedPayroll?.start_date} - ${selectedPayroll?.end_date}`} &bull; Total: {formatCurrency(selectedPayroll?.total_amount)}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsDetailModalOpen(false)}
                                    className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <div className="mt-4 flex-1 overflow-y-auto">
                                {loadingItems ? (
                                    <div className="py-16 text-center text-xs font-semibold text-slate-500">
                                        Cargando desglose de colaboradores...
                                    </div>
                                ) : (
                                    <table className="w-full text-left text-sm">
                                        <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                            <tr>
                                                <th className="px-4 py-3">Colaborador</th>
                                                <th className="px-4 py-3">Cargo</th>
                                                <th className="px-4 py-3 text-right">Devengado</th>
                                                <th className="px-4 py-3 text-right">Deducción (5.91%)</th>
                                                <th className="px-4 py-3 text-right">Neto a Cobrar</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                            {payrollItems.map(item => (
                                                <tr key={item.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                                    <td className="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                                        {item.employee_name}
                                                    </td>
                                                    <td className="px-4 py-3 text-xs text-slate-500">
                                                        {item.position}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                                                        {formatCurrency(item.base_amount)}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-mono text-xs text-rose-600 dark:text-rose-400">
                                                        -{formatCurrency(item.deductions)}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                        {formatCurrency(item.net_amount)}
                                                    </td>
                                                </tr>
                                            ))}
                                            {payrollItems.length === 0 && (
                                                <tr>
                                                    <td colSpan={5} className="py-8 text-center text-xs text-slate-400">
                                                        No hay ítems registrados en este lote.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                )}
                            </div>

                            <div className="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                                <span className="font-mono text-xs text-slate-500">
                                    Total Lote: {formatCurrency(selectedPayroll?.total_amount)}
                                </span>
                                <button
                                    type="button"
                                    onClick={() => setIsDetailModalOpen(false)}
                                    className="rounded-lg bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    Cerrar Desglose
                                </button>
                            </div>
                        </div>
                    </div>, document.body)
                )}
            </div>
        </AuthenticatedLayout>
    );
}
