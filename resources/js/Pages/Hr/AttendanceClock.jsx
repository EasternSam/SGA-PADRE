import { X } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AttendanceClock({
    attendances,
    employees = [],
    selectedDate,
    filters = {},
    stats = {},
}) {
    const [modalOpen, setModalOpen] = useState(false);
    const [date, setDate] = useState(selectedDate);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');

    const form = useForm({
        biometric_id: employees[0]?.biometric_id || '',
        punch_time: `${new Date().toISOString().split('T')[0]}T${new Date().toTimeString().slice(0, 5)}`,
        punch_type: 'check_in',
        device_serial: 'MANUAL-ADMIN',
    });

    const handleFilterChange = (newDate, newSearch) => {
        setDate(newDate);
        router.get('/admin/hr/attendances', {
            date: newDate,
            search: newSearch,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleCreatePunch = (e) => {
        e.preventDefault();
        form.post('/admin/hr/attendances', {
            onSuccess: () => {
                setModalOpen(false);
                form.reset();
            },
        });
    };

    const handleDeletePunch = (att) => {
        if (confirm('¿Eliminar este registro de ponche?')) {
            router.delete(`/admin/hr/attendances/${att.id}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AuthenticatedLayout title="Control de Asistencia (Reloj)">
            <Head title="Control Biométrico & Reloj RH - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            Control Biométrico & Reloj de Personal
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Link
                            href="/admin/hr/employees"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.98]"
                        >
                            Directorio Empleados
                        </Link>
                        <button
                            type="button"
                            onClick={() => setModalOpen(true)}
                            className="inline-flex items-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            + Registrar Ponche Manual
                        </button>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Ponches del Día</span>
                            <span className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_punches || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Registros procesados hoy</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Empleados Presentes</span>
                            <span className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {stats.unique_employees || 0}
                        </div>
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Personal en planta</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Entradas Registradas</span>
                            <span className="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            {stats.entries_count || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Marcaciones de entrada</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Salidas Registradas</span>
                            <span className="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {stats.exits_count || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Marcaciones de salida</div>
                    </div>
                </div>

                {/* Date and Search Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div className="flex items-center gap-3 w-full sm:w-auto">
                        <label className="text-xs font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">
                            Fecha de Registro:
                        </label>
                        <input
                            type="date"
                            value={date}
                            onChange={e => handleFilterChange(e.target.value, searchTerm)}
                            className="rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                        />
                    </div>

                    <div className="w-full sm:w-80 relative">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && handleFilterChange(date, searchTerm)}
                            placeholder="Buscar empleado o ID biométrico..."
                            className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs pl-8 pr-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                        />
                        <svg className="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {/* Attendance Punches Table */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    {attendances.data.length === 0 ? (
                        <div className="p-12 text-center text-gray-500">
                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 className="mt-3 text-base font-semibold text-gray-900 dark:text-white">Sin ponches en esta fecha</h3>
                            <p className="mt-1 text-sm text-gray-500">No se encontraron marcaciones para el día seleccionado.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-gray-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-gray-800/30">
                                        <th className="py-3 px-4">Empleado</th>
                                        <th className="py-3 px-4">ID Biométrico</th>
                                        <th className="py-3 px-4">Hora de Marcación</th>
                                        <th className="py-3 px-4">Tipo</th>
                                        <th className="py-3 px-4">Dispositivo</th>
                                        <th className="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    {attendances.data.map(item => {
                                        const empName = item.employee?.user?.name || 'Empleado No Vinculado';
                                        const isEntry = item.punch_type === 'check_in' || item.punch_type === 'Entrada';
                                        const punchDate = new Date(item.punch_time);

                                        return (
                                            <tr key={item.id} className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition">
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="h-8 w-8 rounded-full bg-brand-primary/10 text-brand-primary dark:bg-blue-900/30 dark:text-blue-300 font-bold flex items-center justify-center shrink-0 text-xs">
                                                            {empName[0]}
                                                        </div>
                                                        <div>
                                                            <div className="font-bold text-gray-900 dark:text-white">
                                                                {empName}
                                                            </div>
                                                            <div className="text-[11px] text-gray-400 mt-0.5">
                                                                {item.employee?.position || item.employee?.department || 'Personal'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="py-3 px-4">
                                                    <span className="font-mono font-semibold px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                        {item.biometric_id}
                                                    </span>
                                                </td>

                                                <td className="py-3 px-4">
                                                    <div className="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                        <svg className="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {punchDate.toLocaleTimeString('es-DO', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
                                                    </div>
                                                    <div className="text-[10px] text-gray-400">
                                                        {punchDate.toLocaleDateString('es-DO')}
                                                    </div>
                                                </td>

                                                <td className="py-3 px-4">
                                                    {isEntry ? (
                                                        <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                            ⬇ Entrada
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300">
                                                            ⬆ Salida
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4 text-gray-500 font-mono text-[11px]">
                                                    {item.device_serial || 'RELOJ-PRINCIPAL'}
                                                </td>

                                                <td className="py-3 px-4 text-right">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDeletePunch(item)}
                                                        className="p-1 text-gray-400 hover:text-red-600"
                                                        title="Eliminar Ponche"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Pagination */}
                    {attendances.links && attendances.links.length > 3 && (
                        <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <span className="text-xs text-gray-500">
                                Mostrando {attendances.from || 0} a {attendances.to || 0} de {attendances.total} registros
                            </span>
                            <div className="flex gap-1">
                                {attendances.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        preserveScroll
                                        className={`px-3 py-1 rounded-lg text-xs font-semibold ${
                                            link.active
                                                ? 'bg-brand-primary text-white'
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                        } ${!link.url ? 'opacity-40 cursor-not-allowed' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal: Registrar Ponche Manual */}
            {modalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-md rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                Registrar Marcación Manual
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        <form onSubmit={handleCreatePunch} className="space-y-4 mt-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Empleado *
                                </label>
                                <select
                                    value={form.data.biometric_id}
                                    onChange={e => form.setData('biometric_id', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">Seleccione un empleado...</option>
                                    {employees.map(emp => (
                                        <option key={emp.id} value={emp.biometric_id || emp.id}>
                                            {emp.name} ({emp.position || 'Personal'}) - ID: {emp.biometric_id}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Fecha y Hora de Marcación *
                                </label>
                                <input
                                    type="datetime-local"
                                    value={form.data.punch_time}
                                    onChange={e => form.setData('punch_time', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Tipo de Marcación *
                                </label>
                                <select
                                    value={form.data.punch_type}
                                    onChange={e => form.setData('punch_type', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="check_in">⬇ Entrada Regular</option>
                                    <option value="check_out">⬆ Salida</option>
                                </select>
                            </div>

                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {form.processing ? 'Registrando...' : 'Registrar Ponche'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
