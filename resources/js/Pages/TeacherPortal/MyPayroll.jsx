import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    DollarSign,
    ArrowLeft,
    Receipt,
    Calendar,
    Printer,
    CheckCircle2,
    Clock,
    Briefcase
} from 'lucide-react';

export default function MyPayroll({
    auth,
    employee,
    items = [],
    totalPaid = 0
}) {
    const formatCurrency = (val) => {
        return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(val || 0);
    };

    return (
        <AuthenticatedLayout auth={auth} title="Mis Volantes de Nómina">
            <Head title="Mis Recibos de Nómina | Docente" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            href={route('teacher.dashboard')}
                            className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Volver al Dashboard
                        </Link>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Mis Volantes de Nómina & Salario
                        </h1>
                        
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Salario Base Registrado
                        </span>
                        <div className="mt-2 font-mono text-3xl font-black text-slate-900 dark:text-white">
                            {formatCurrency(employee?.base_salary)}
                        </div>
                        <p className="mt-1 text-[11px] text-slate-400">Modalidad: {employee?.contract_type}</p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Total Neto Percibido Histórico
                        </span>
                        <div className="mt-2 font-mono text-3xl font-black text-emerald-600 dark:text-emerald-400">
                            {formatCurrency(totalPaid)}
                        </div>
                        <p className="mt-1 text-[11px] text-slate-400">{items.length} liquidaciones procesadas</p>
                    </div>
                </div>

                {/* Payroll Receipts Table */}
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 className="text-base font-bold text-slate-900 dark:text-white">
                            Historial de Pagos & Volantes
                        </h3>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                <tr>
                                    <th className="px-5 py-3.5">Lote de Nómina</th>
                                    <th className="px-5 py-3.5">Período Liquidado</th>
                                    <th className="px-5 py-3.5 text-right">Salario Bruto</th>
                                    <th className="px-5 py-3.5 text-right">Deducciones (TSS)</th>
                                    <th className="px-5 py-3.5 text-right">Monto Neto</th>
                                    <th className="px-5 py-3.5 text-center">Estado</th>
                                    <th className="px-5 py-3.5 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {items.map(item => (
                                    <tr key={item.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                        <td className="px-5 py-3.5 font-bold text-slate-900 dark:text-white">
                                            {item.payroll_name}
                                        </td>
                                        <td className="px-5 py-3.5 text-xs text-slate-500">
                                            {item.period}
                                        </td>
                                        <td className="px-5 py-3.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                                            {formatCurrency(item.base_amount)}
                                        </td>
                                        <td className="px-5 py-3.5 text-right font-mono text-xs text-rose-600 dark:text-rose-400">
                                            -{formatCurrency(item.deductions)}
                                        </td>
                                        <td className="px-5 py-3.5 text-right font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                            {formatCurrency(item.net_amount)}
                                        </td>
                                        <td className="px-5 py-3.5 text-center">
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                                item.status === 'Pagado'
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                    : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                                            }`}>
                                                {item.status}
                                            </span>
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
                                {items.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="py-12 text-center text-sm text-slate-500">
                                            No se registran volantes de nómina emitidos aún.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
