import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function FinanceDashboard({ kpis, chartData, payments, concepts, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.statusFilter || '');
    const [dateFilter, setDateFilter] = useState(filters?.dateFilter || 'this_month');

    const handleFilterChange = (newDateFilter, newStatus, newSearch) => {
        router.get('/admin/finance/dashboard', {
            dateFilter: newDateFilter ?? dateFilter,
            statusFilter: newStatus ?? statusFilter,
            search: newSearch ?? search,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Cálculos para gráfico de barras SVG
    const maxBar = Math.max(...(chartData?.income || [1000]), ...(chartData?.pending || [500]), 1000);
    const chartHeight = 220;
    const chartWidth = 650;
    const paddingX = 45;
    const availableWidth = chartWidth - paddingX * 2;
    const numBars = chartData?.labels?.length || 6;
    const groupWidth = availableWidth / numBars;
    const barWidth = 18;

    return (
        <AuthenticatedLayout title="Panel Financiero">
            <Head title="Panel Financiero & Ingresos - SGA" />

            <div className="space-y-6 sm:space-y-8">
                {/* ── ENCABEZADO ── */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-gray-200/60 dark:border-white/[0.06]">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                            <a href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</a>
                            <span>/</span>
                            <span className="text-gray-600 dark:text-gray-300">Finanzas</span>
                        </div>
                        <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <svg className="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Panel Financiero & Aranceles
                        </h1>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        {/* Botón Ir a Terminal de Caja POS */}
                        <a 
                            href="/admin/pos"
                            className="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95"
                        >
                            <svg className="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Terminal de Caja (POS)</span>
                        </a>

                        {/* Filtro Rápido de Fecha */}
                        <div className="flex bg-white dark:bg-[#161622] rounded-xl shadow-xs p-1 border border-gray-200 dark:border-gray-800">
                            {[
                                { key: 'all', label: 'Todo' },
                                { key: 'this_month', label: 'Este Mes' },
                                { key: 'today', label: 'Hoy' },
                            ].map((tab) => (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => {
                                        setDateFilter(tab.key);
                                        handleFilterChange(tab.key, statusFilter, search);
                                    }}
                                    className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all ${
                                        dateFilter === tab.key
                                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-xs'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* ── 1. KPIs FINANCIEROS ── */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {/* Ingresos Totales */}
                    <div className="bg-white dark:bg-[#161622] p-6 rounded-2xl shadow-xs border border-emerald-100 dark:border-emerald-950/40 relative overflow-hidden group ring-1 ring-gray-900/5 dark:ring-white/10">
                        <div className="absolute right-0 top-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110" />
                        <p className="text-xs font-bold text-gray-400 uppercase tracking-wider relative z-10">Ingresos Totales</p>
                        <h3 className="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 relative z-10 tracking-tight">
                            RD$ {(kpis?.totalIncome || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </h3>
                        <div className="mt-3 flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 relative z-10">
                            <span className="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 px-2.5 py-0.5 rounded-full">
                                Cobrado Efectivo
                            </span>
                        </div>
                    </div>

                    {/* Cuentas por Cobrar */}
                    <div className="bg-white dark:bg-[#161622] p-6 rounded-2xl shadow-xs border border-amber-100 dark:border-amber-950/40 relative overflow-hidden group ring-1 ring-gray-900/5 dark:ring-white/10">
                        <div className="absolute right-0 top-0 w-24 h-24 bg-amber-50 dark:bg-amber-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110" />
                        <p className="text-xs font-bold text-gray-400 uppercase tracking-wider relative z-10">Cuentas por Cobrar</p>
                        <h3 className="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 relative z-10 tracking-tight">
                            RD$ {(kpis?.totalPending || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </h3>
                        <div className="mt-3 flex items-center text-xs font-semibold text-amber-600 dark:text-amber-400 relative z-10">
                            <span className="bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 px-2.5 py-0.5 rounded-full">
                                Pendiente
                            </span>
                        </div>
                    </div>

                    {/* Movimientos */}
                    <div className="bg-white dark:bg-[#161622] p-6 rounded-2xl shadow-xs border border-blue-100 dark:border-blue-950/40 relative overflow-hidden group ring-1 ring-gray-900/5 dark:ring-white/10">
                        <div className="absolute right-0 top-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110" />
                        <p className="text-xs font-bold text-gray-400 uppercase tracking-wider relative z-10">Transacciones</p>
                        <h3 className="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 relative z-10 tracking-tight">
                            {(kpis?.transactionsCount || 0).toLocaleString()}
                        </h3>
                        <div className="mt-3 flex items-center text-xs font-semibold text-blue-600 dark:text-blue-400 relative z-10">
                            <span className="bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 px-2.5 py-0.5 rounded-full">
                                Registros Totales
                            </span>
                        </div>
                    </div>
                </div>

                {/* ── 2. GRÁFICO DE FLUJO DE CAJA ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 sm:p-8 relative overflow-hidden ring-1 ring-gray-900/5 dark:ring-white/10">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                        <div>
                            <h3 className="text-base sm:text-lg font-bold text-gray-900 dark:text-white tracking-tight">
                                Flujo de Caja (Últimos 6 Meses)
                            </h3>
                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Comparativa mensual de ingresos cobrados vs montos en espera
                            </p>
                        </div>

                        <div className="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/60 p-1.5 rounded-xl border border-gray-200/60 dark:border-gray-800 text-xs font-semibold">
                            <div className="flex items-center gap-2 px-2.5 py-1 rounded-lg bg-white dark:bg-[#161622] shadow-xs">
                                <span className="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span className="text-gray-700 dark:text-gray-300">Cobrado</span>
                            </div>
                            <div className="flex items-center gap-2 px-2.5 py-1 rounded-lg bg-white dark:bg-[#161622] shadow-xs">
                                <span className="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span className="text-gray-700 dark:text-gray-300">Pendiente</span>
                            </div>
                        </div>
                    </div>

                    {/* Gráfico de barras SVG */}
                    <div className="w-full overflow-x-auto">
                        <div className="min-w-[600px]">
                            <svg viewBox={`0 0 ${chartWidth} ${chartHeight + 35}`} className="w-full h-64 overflow-visible">
                                {/* Cuadrícula horizontal */}
                                {[0, 0.25, 0.5, 0.75, 1].map((pct, idx) => {
                                    const y = chartHeight - pct * (chartHeight - 30) - 15;
                                    return (
                                        <g key={`grid-fin-${idx}`}>
                                            <line 
                                                x1={paddingX} 
                                                y1={y} 
                                                x2={chartWidth - paddingX} 
                                                y2={y} 
                                                stroke="currentColor" 
                                                className="text-gray-100 dark:text-gray-800" 
                                                strokeDasharray="4 4" 
                                            />
                                            <text 
                                                x={paddingX - 8} 
                                                y={y + 4} 
                                                textAnchor="end" 
                                                className="text-[10px] fill-gray-400 font-mono"
                                            >
                                                ${Math.round((pct * maxBar) / 1000)}k
                                            </text>
                                        </g>
                                    );
                                })}

                                {/* Barras */}
                                {chartData?.labels?.map((label, idx) => {
                                    const groupX = paddingX + idx * groupWidth;
                                    const incVal = chartData.income[idx] || 0;
                                    const penVal = chartData.pending[idx] || 0;

                                    const hInc = (incVal / maxBar) * (chartHeight - 30);
                                    const hPen = (penVal / maxBar) * (chartHeight - 30);

                                    const yInc = chartHeight - hInc - 15;
                                    const yPen = chartHeight - hPen - 15;

                                    return (
                                        <g key={`bar-${idx}`} className="group cursor-pointer">
                                            {/* Barra Ingreso */}
                                            <rect
                                                x={groupX + groupWidth / 2 - barWidth - 2}
                                                y={yInc}
                                                width={barWidth}
                                                height={hInc}
                                                rx="4"
                                                className="fill-emerald-500 hover:fill-emerald-600 transition-colors"
                                            />
                                            {/* Barra Pendiente */}
                                            <rect
                                                x={groupX + groupWidth / 2 + 2}
                                                y={yPen}
                                                width={barWidth}
                                                height={hPen}
                                                rx="4"
                                                className="fill-amber-400 hover:fill-amber-500 transition-colors"
                                            />
                                            {/* Etiqueta X */}
                                            <text
                                                x={groupX + groupWidth / 2}
                                                y={chartHeight + 16}
                                                textAnchor="middle"
                                                className="text-[11px] fill-gray-400 dark:fill-gray-500 font-medium"
                                            >
                                                {label}
                                            </text>
                                        </g>
                                    );
                                })}
                            </svg>
                        </div>
                    </div>
                </div>

                {/* ── 3. TABLA DE TRANSACCIONES & PRECIOS ── */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    {/* Columna Izquierda: Tabla de Transacciones (2 cols) */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs ring-1 ring-gray-900/5 dark:ring-white/10 overflow-hidden border border-gray-100 dark:border-gray-800">
                            {/* Filtros de la Tabla */}
                            <div className="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <h3 className="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                    Transacciones Recientes
                                </h3>

                                <div className="flex flex-col sm:flex-row items-center gap-2.5">
                                    <div className="relative w-full sm:w-56">
                                        <svg className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input
                                            type="text"
                                            value={search}
                                            onChange={(e) => {
                                                setSearch(e.target.value);
                                                handleFilterChange(dateFilter, statusFilter, e.target.value);
                                            }}
                                            placeholder="Buscar alumno o ref..."
                                            className="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-primary"
                                        />
                                    </div>

                                    <select
                                        value={statusFilter}
                                        onChange={(e) => {
                                            setStatusFilter(e.target.value);
                                            handleFilterChange(dateFilter, e.target.value, search);
                                        }}
                                        className="w-full sm:w-auto px-3 py-1.5 text-xs rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-primary"
                                    >
                                        <option value="">Todos los estados</option>
                                        <option value="Completado">Completado</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Fallido">Fallido</option>
                                    </select>
                                </div>
                            </div>

                            {/* Contenido de la Tabla */}
                            <div className="overflow-x-auto">
                                <table className="w-full text-xs text-left">
                                    <thead className="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800">
                                        <tr>
                                            <th className="px-6 py-3.5">Fecha</th>
                                            <th className="px-6 py-3.5">Estudiante</th>
                                            <th className="px-6 py-3.5">Concepto</th>
                                            <th className="px-6 py-3.5">Método</th>
                                            <th className="px-6 py-3.5 text-center">Estado</th>
                                            <th className="px-6 py-3.5 text-right">Monto</th>
                                            <th className="px-6 py-3.5 text-right">Recibo</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-gray-800/80 bg-white dark:bg-[#161622]">
                                        {payments && payments.length > 0 ? (
                                            payments.map((p) => (
                                                <tr key={p.id} className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                                    <td className="px-6 py-3.5 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                        {p.date}
                                                        <span className="block text-[10px] text-gray-400 font-normal">{p.time}</span>
                                                    </td>
                                                    <td className="px-6 py-3.5">
                                                        <div className="flex items-center gap-2.5">
                                                            <div className="h-7 w-7 rounded-full bg-brand-primary text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                                                                {p.student_name.charAt(0)}
                                                            </div>
                                                            <div className="min-w-0">
                                                                <p className="font-semibold text-gray-900 dark:text-white truncate">{p.student_name}</p>
                                                                <p className="text-[10px] text-gray-400 truncate">{p.student_email}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-3.5">
                                                        <p className="font-medium text-gray-800 dark:text-gray-200">{p.concept_name}</p>
                                                        {p.transaction_id && (
                                                            <span className="text-[10px] text-gray-400 font-mono">Ref: {p.transaction_id}</span>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-3.5">
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                            {p.gateway}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-3.5 text-center">
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ${
                                                            p.status === 'Completado' || p.status === 'Pagado'
                                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'
                                                                : p.status === 'Pendiente'
                                                                ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800'
                                                                : 'bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-400 border border-red-200 dark:border-red-800'
                                                        }`}>
                                                            {p.status}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white font-mono">
                                                        RD$ {p.amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-6 py-3.5 text-right">
                                                        <button
                                                            type="button"
                                                            onClick={() => window.open(`/finance/ticket/${p.id}`, 'Ticket', 'width=420,height=620')}
                                                            className="text-gray-400 hover:text-brand-primary dark:hover:text-blue-400 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                                            title="Imprimir Recibo"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-12 text-center text-gray-400">
                                                    No se encontraron transacciones registradas.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {/* Columna Derecha: Catálogo de Precios & Aranceles (1 col) */}
                    <div className="space-y-6">
                        <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs ring-1 ring-gray-900/5 dark:ring-white/10 p-6 border border-gray-100 dark:border-gray-800">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <div className="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600">
                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    Aranceles y Conceptos
                                </h3>

                                <a 
                                    href="/admin/finance/payment-concepts" 
                                    className="text-xs font-bold text-brand-primary dark:text-blue-400 hover:underline"
                                >
                                    Ver todos →
                                </a>
                            </div>

                            <div className="divide-y divide-gray-100 dark:divide-gray-800">
                                {concepts && concepts.length > 0 ? (
                                    concepts.map((c) => (
                                        <div key={c.id} className="py-2.5 flex items-center justify-between text-xs">
                                            <div className="min-w-0 pr-2">
                                                <p className="font-semibold text-gray-800 dark:text-gray-200 truncate">{c.name}</p>
                                                <span className="text-[10px] text-gray-400">
                                                    {c.is_fixed_amount ? 'Tarifa fija' : 'Monto variable'}
                                                </span>
                                            </div>
                                            <span className="font-mono font-bold text-gray-900 dark:text-white shrink-0">
                                                RD$ {c.amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-xs text-gray-400 py-4">No hay conceptos configurados.</p>
                                )}
                            </div>

                            <div className="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <a 
                                    href="/admin/finance/payment-concepts" 
                                    className="w-full py-2 px-3 text-xs font-semibold bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl transition flex items-center justify-center gap-1.5 border border-gray-200 dark:border-gray-700"
                                >
                                    <span>Administrar Aranceles</span>
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
