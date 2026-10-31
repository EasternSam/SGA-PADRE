import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    ArrowLeft,
    User,
    Mail,
    Shield,
    Fingerprint,
    Briefcase,
    Building2,
    Calendar,
    DollarSign,
    Clock,
    FileText,
    Award,
    CheckCircle2,
    XCircle,
    Key,
    Ban,
    Plus,
    Edit3,
    X,
    Save,
    CreditCard,
    AlertTriangle,
    FileSpreadsheet
} from 'lucide-react';

export default function EmployeeProfile({
    auth,
    employee,
    attendances = [],
    payrollHistory = [],
    events = [],
    roles = []
}) {
    const [activeTab, setActiveTab] = useState('dossier'); // dossier, attendance, payroll, events

    // Quick Update states
    const [selectedRole, setSelectedRole] = useState(employee.role);
    const [biometricId, setBiometricId] = useState(employee.biometric_id || '');
    const [updatingRole, setUpdatingRole] = useState(false);
    const [updatingBiometric, setUpdatingBiometric] = useState(false);

    // Salary Modal
    const [isSalaryModalOpen, setIsSalaryModalOpen] = useState(false);
    const [salaryForm, setSalaryForm] = useState({
        contract_type: employee.contract_type || 'Mensual',
        base_salary: employee.base_salary || 0,
        hourly_rate: employee.hourly_rate || 0,
    });
    const [savingSalary, setSavingSalary] = useState(false);

    // Event Modal
    const [isEventModalOpen, setIsEventModalOpen] = useState(false);
    const [eventForm, setEventForm] = useState({
        event_type: 'bonus',
        event_date: new Date().toISOString().split('T')[0],
        event_end_date: '',
        event_amount: '',
        event_score: '5',
        event_description: '',
    });
    const [savingEvent, setSavingEvent] = useState(false);

    const formatCurrency = (val) => {
        return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(val || 0);
    };

    const handleUpdateRole = () => {
        setUpdatingRole(true);
        router.post(route('admin.hr.employees.role', employee.id), {
            role_name: selectedRole,
        }, {
            preserveScroll: true,
            onFinish: () => setUpdatingRole(false),
        });
    };

    const handleUpdateBiometric = () => {
        setUpdatingBiometric(true);
        router.post(route('admin.hr.employees.biometric', employee.id), {
            biometric_id: biometricId || null,
        }, {
            preserveScroll: true,
            onFinish: () => setUpdatingBiometric(false),
        });
    };

    const handleSaveSalary = (e) => {
        e.preventDefault();
        setSavingSalary(true);
        router.post(route('admin.hr.employees.salary', employee.id), salaryForm, {
            preserveScroll: true,
            onSuccess: () => {
                setIsSalaryModalOpen(false);
                setSavingSalary(false);
            },
            onError: () => setSavingSalary(false),
        });
    };

    const handleSaveEvent = (e) => {
        e.preventDefault();
        setSavingEvent(true);
        router.post(route('admin.hr.employees.event', employee.id), eventForm, {
            preserveScroll: true,
            onSuccess: () => {
                setIsEventModalOpen(false);
                setSavingEvent(false);
            },
            onError: () => setSavingEvent(false),
        });
    };

    const handleRegeneratePin = () => {
        if (!confirm('¿Desea generar un nuevo PIN de 4 dígitos para que este colaborador marque en el quiosco?')) return;
        router.post(route('admin.hr.employees.pin', employee.id), {}, { preserveScroll: true });
    };

    const handleBanUser = () => {
        if (!confirm('¿Está seguro de que desea suspender y revocar todo el acceso al sistema a este colaborador?')) return;
        router.post(route('admin.hr.employees.ban', employee.id), {}, { preserveScroll: true });
    };

    const totalPaidHistorical = payrollHistory.reduce((acc, curr) => acc + (curr.net_amount || 0), 0);

    return (
        <AuthenticatedLayout auth={auth} title={`Expediente: ${employee.name}`}>
            <Head title={`Expediente: ${employee.name}`} />

            <div className="space-y-6 pb-20">
                {/* Header Back Link & Actions */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={route('admin.hr.employees')}
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Volver al Directorio de Personal
                    </Link>

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={handleRegeneratePin}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Key className="h-3.5 w-3.5 text-amber-500" />
                            Generar PIN Quiosco
                        </button>

                        <button
                            type="button"
                            onClick={handleBanUser}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 shadow-sm hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300"
                        >
                            <Ban className="h-3.5 w-3.5 text-rose-600" />
                            Suspender Acceso
                        </button>
                    </div>
                </div>

                {/* Hero Profile Card */}
                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-primary text-xl font-black text-white shadow-md">
                                {employee.name?.substring(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <div className="flex items-center gap-2">
                                    <h1 className="text-xl font-black text-slate-900 dark:text-white">
                                        {employee.name}
                                    </h1>
                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                        employee.status === 'Activo'
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                            : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                                    }`}>
                                        {employee.status}
                                    </span>
                                </div>
                                <p className="text-sm font-medium text-slate-600 dark:text-slate-400">
                                    {employee.position || 'Sin cargo asignado'} &bull; <span className="text-slate-400">{employee.department || 'General'}</span>
                                </p>
                                <p className="text-xs text-slate-400">
                                    {employee.email} &bull; Contratado el {employee.hire_date || 'No registrada'}
                                </p>
                            </div>
                        </div>

                        {/* Top quick stats */}
                        <div className="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 md:border-t-0 md:pt-0 dark:border-slate-800">
                            <div className="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/60 min-w-[120px]">
                                <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Salario Actual</span>
                                <div className="mt-1 font-mono text-sm font-bold text-slate-900 dark:text-white">
                                    {employee.contract_type === 'Mensual'
                                        ? formatCurrency(employee.base_salary)
                                        : `${formatCurrency(employee.hourly_rate)} / hr`
                                    }
                                </div>
                            </div>

                            <div className="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/60 min-w-[120px]">
                                <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Devengado</span>
                                <div className="mt-1 font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    {formatCurrency(totalPaidHistorical)}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Navigation Tabs */}
                    <div className="mt-8 flex gap-2 border-b border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            onClick={() => setActiveTab('dossier')}
                            className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-bold transition ${
                                activeTab === 'dossier'
                                    ? 'border-brand-primary text-brand-primary dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                            }`}
                        >
                            <User className="h-4 w-4" />
                            Expediente & Parámetros
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab('attendance')}
                            className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-bold transition ${
                                activeTab === 'attendance'
                                    ? 'border-brand-primary text-brand-primary dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                            }`}
                        >
                            <Clock className="h-4 w-4" />
                            Reloj Biométrico ({attendances.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab('payroll')}
                            className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-bold transition ${
                                activeTab === 'payroll'
                                    ? 'border-brand-primary text-brand-primary dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                            }`}
                        >
                            <CreditCard className="h-4 w-4" />
                            Nóminas & Pagos ({payrollHistory.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab('events')}
                            className={`flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-bold transition ${
                                activeTab === 'events'
                                    ? 'border-brand-primary text-brand-primary dark:border-blue-400 dark:text-blue-400'
                                    : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'
                            }`}
                        >
                            <FileText className="h-4 w-4" />
                            Bitácora de Novedades ({events.length})
                        </button>
                    </div>
                </div>

                {/* Tab 1: Dossier & Parameters */}
                {activeTab === 'dossier' && (
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {/* Access & Biometric Card */}
                        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5">
                            <h3 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <Shield className="h-4 w-4 text-brand-primary" />
                                Acceso al Sistema & Control de Asistencia
                            </h3>

                            <div className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                        Nivel de Permisos / Rol Spatie
                                    </label>
                                    <div className="flex items-center gap-2">
                                        <select
                                            value={selectedRole}
                                            onChange={(e) => setSelectedRole(e.target.value)}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            {roles.map(r => (
                                                <option key={r} value={r}>{r}</option>
                                            ))}
                                        </select>
                                        <button
                                            type="button"
                                            onClick={handleUpdateRole}
                                            disabled={updatingRole || selectedRole === employee.role}
                                            className="inline-flex items-center gap-1 rounded-lg bg-brand-primary px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#082260] disabled:opacity-50 transition"
                                        >
                                            {updatingRole ? 'Sincronizando...' : 'Actualizar Rol'}
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                        ID Biométrico (Reloj Marcador Físico)
                                    </label>
                                    <div className="flex items-center gap-2">
                                        <div className="relative w-full">
                                            <Fingerprint className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                                            <input
                                                type="number"
                                                value={biometricId}
                                                onChange={(e) => setBiometricId(e.target.value)}
                                                placeholder="Ej. 101"
                                                className="w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            onClick={handleUpdateBiometric}
                                            disabled={updatingBiometric}
                                            className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            {updatingBiometric ? 'Guardando...' : 'Asignar ID'}
                                        </button>
                                    </div>
                                    <p className="mt-1 text-[11px] text-slate-400">
                                        Este ID se vincula con el reloj de huella o facial institucional.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Salary Parameters Card */}
                        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5">
                            <div className="flex items-center justify-between">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <DollarSign className="h-4 w-4 text-emerald-600" />
                                    Condiciones Contractuales & Salario
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setIsSalaryModalOpen(true)}
                                    className="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <Edit3 className="h-3.5 w-3.5" />
                                    Modificar Salario
                                </button>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="rounded-lg bg-slate-50 p-3.5 dark:bg-slate-800/40">
                                    <span className="text-[11px] font-semibold text-slate-500 uppercase">Modalidad</span>
                                    <div className="mt-1 font-bold text-slate-900 dark:text-white">
                                        {employee.contract_type}
                                    </div>
                                </div>

                                <div className="rounded-lg bg-slate-50 p-3.5 dark:bg-slate-800/40">
                                    <span className="text-[11px] font-semibold text-slate-500 uppercase">
                                        {employee.contract_type === 'Mensual' ? 'Salario Base Mensual' : 'Tarifa por Hora'}
                                    </span>
                                    <div className="mt-1 font-mono font-bold text-slate-900 dark:text-white">
                                        {employee.contract_type === 'Mensual'
                                            ? formatCurrency(employee.base_salary)
                                            : formatCurrency(employee.hourly_rate)
                                        }
                                    </div>
                                </div>

                                <div className="rounded-lg bg-slate-50 p-3.5 dark:bg-slate-800/40">
                                    <span className="text-[11px] font-semibold text-slate-500 uppercase">Deducción de Ley Estimada</span>
                                    <div className="mt-1 font-mono font-bold text-rose-600 dark:text-rose-400">
                                        {formatCurrency(employee.base_salary * 0.0591)} (5.91%)
                                    </div>
                                    <span className="text-[10px] text-slate-400">TSS (SFS 3.04% + AFP 2.87%)</span>
                                </div>

                                <div className="rounded-lg bg-slate-50 p-3.5 dark:bg-slate-800/40">
                                    <span className="text-[11px] font-semibold text-slate-500 uppercase">Neto Estimado Mensual</span>
                                    <div className="mt-1 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        {formatCurrency(employee.base_salary * (1 - 0.0591))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Tab 2: Biometric Attendance History */}
                {activeTab === 'attendance' && (
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="border-b border-slate-100 bg-slate-50/50 px-6 py-3 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900/60">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Últimas {attendances.length} Marcaciones Registradas
                            </span>
                            <Link
                                href={route('admin.hr.attendances')}
                                className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                            >
                                Ver Reloj Global &rarr;
                            </Link>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                    <tr>
                                        <th className="px-5 py-3">Fecha & Hora</th>
                                        <th className="px-5 py-3 text-center">Tipo de Ponche</th>
                                        <th className="px-5 py-3">Dispositivo / Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {attendances.map(a => (
                                        <tr key={a.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                            <td className="px-5 py-3 font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">
                                                {a.punch_time}
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                                    a.punch_type == 0
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                        : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300'
                                                }`}>
                                                    {a.punch_type_label}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-xs text-slate-500">
                                                {a.device_ip}
                                            </td>
                                        </tr>
                                    ))}
                                    {attendances.length === 0 && (
                                        <tr>
                                            <td colSpan={3} className="py-8 text-center text-xs text-slate-400">
                                                No hay registros de asistencia biométrica recientes para este empleado.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Tab 3: Payroll History */}
                {activeTab === 'payroll' && (
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="border-b border-slate-100 bg-slate-50/50 px-6 py-3 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900/60">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Histórico de Liquidaciones de Nómina
                            </span>
                            <span className="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                Total: {formatCurrency(totalPaidHistorical)}
                            </span>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                    <tr>
                                        <th className="px-5 py-3">Lote de Nómina</th>
                                        <th className="px-5 py-3">Período</th>
                                        <th className="px-5 py-3 text-right">Salario Base</th>
                                        <th className="px-5 py-3 text-right">Deducciones Ley (TSS)</th>
                                        <th className="px-5 py-3 text-right">Neto Percibido</th>
                                        <th className="px-5 py-3 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {payrollHistory.map(item => (
                                        <tr key={item.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                            <td className="px-5 py-3 font-semibold text-slate-900 dark:text-white">
                                                {item.payroll_name}
                                            </td>
                                            <td className="px-5 py-3 text-xs text-slate-500">
                                                {item.period}
                                            </td>
                                            <td className="px-5 py-3 text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                                                {formatCurrency(item.base_amount)}
                                            </td>
                                            <td className="px-5 py-3 text-right font-mono text-xs text-rose-600 dark:text-rose-400">
                                                -{formatCurrency(item.deductions)}
                                            </td>
                                            <td className="px-5 py-3 text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                {formatCurrency(item.net_amount)}
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold ${
                                                    item.status === 'Pagado'
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                                                }`}>
                                                    {item.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                    {payrollHistory.length === 0 && (
                                        <tr>
                                            <td colSpan={6} className="py-8 text-center text-xs text-slate-400">
                                                Aún no se han generado liquidaciones de nómina para este colaborador.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Tab 4: Events Log */}
                {activeTab === 'events' && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                Registro de Novedades, Bonificaciones & Licencias
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsEventModalOpen(true)}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-brand-primary px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#082260]"
                            >
                                <Plus className="h-3.5 w-3.5" />
                                Registrar Novedad
                            </button>
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {events.map(ev => (
                                <div key={ev.id} className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-xs font-bold uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {ev.type}
                                        </span>
                                        <span className="text-xs text-slate-400">{ev.event_date}</span>
                                    </div>
                                    <p className="text-xs text-slate-700 dark:text-slate-300">
                                        {ev.description}
                                    </p>
                                    {ev.amount > 0 && (
                                        <div className="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                            Monto: {formatCurrency(ev.amount)}
                                        </div>
                                    )}
                                    {ev.score && (
                                        <div className="text-xs font-semibold text-amber-600">
                                            Calificación de Desempeño: {ev.score} / 5
                                        </div>
                                    )}
                                </div>
                            ))}
                            {events.length === 0 && (
                                <div className="col-span-2 rounded-xl border border-dashed border-slate-200 p-8 text-center text-xs text-slate-400 dark:border-slate-800">
                                    No hay eventos ni novedades asentadas en este expediente.
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Salary Modification Modal */}
                {isSalaryModalOpen && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Actualizar Parámetros Salariales
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setIsSalaryModalOpen(false)}
                                    className="rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>
                            <form onSubmit={handleSaveSalary} className="mt-4 space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Tipo de Contrato
                                    </label>
                                    <select
                                        value={salaryForm.contract_type}
                                        onChange={(e) => setSalaryForm({ ...salaryForm, contract_type: e.target.value })}
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="Mensual">Mensual</option>
                                        <option value="Por Horas">Por Horas</option>
                                    </select>
                                </div>

                                {salaryForm.contract_type === 'Mensual' ? (
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Salario Base Mensual (DOP)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            value={salaryForm.base_salary}
                                            onChange={(e) => setSalaryForm({ ...salaryForm, base_salary: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                ) : (
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Tarifa por Hora (DOP)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            value={salaryForm.hourly_rate}
                                            onChange={(e) => setSalaryForm({ ...salaryForm, hourly_rate: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                )}

                                <div className="flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsSalaryModalOpen(false)}
                                        className="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={savingSalary}
                                        className="rounded-lg bg-brand-primary px-4 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#082260]"
                                    >
                                        {savingSalary ? 'Guardando...' : 'Guardar Parámetros'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>, document.body)
                )}

                {/* Event Registration Modal */}
                {isEventModalOpen && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Registrar Novedad / Evento en Expediente
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setIsEventModalOpen(false)}
                                    className="rounded-lg p-1 text-slate-400 hover:bg-slate-100"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>
                            <form onSubmit={handleSaveEvent} className="mt-4 space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Tipo de Novedad
                                    </label>
                                    <select
                                        value={eventForm.event_type}
                                        onChange={(e) => setEventForm({ ...eventForm, event_type: e.target.value })}
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="bonus">Bonificación / Incentivo</option>
                                        <option value="deduction">Deducción Extraordinaria</option>
                                        <option value="evaluation">Evaluación de Desempeño</option>
                                        <option value="vacation">Período de Vacaciones</option>
                                        <option value="medical">Licencia Médica</option>
                                    </select>
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Fecha Inicio
                                        </label>
                                        <input
                                            type="date"
                                            required
                                            value={eventForm.event_date}
                                            onChange={(e) => setEventForm({ ...eventForm, event_date: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Fecha Fin (Opcional)
                                        </label>
                                        <input
                                            type="date"
                                            value={eventForm.event_end_date}
                                            onChange={(e) => setEventForm({ ...eventForm, event_end_date: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                </div>

                                {['bonus', 'deduction'].includes(eventForm.event_type) && (
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Monto DOP
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            required
                                            placeholder="0.00"
                                            value={eventForm.event_amount}
                                            onChange={(e) => setEventForm({ ...eventForm, event_amount: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                )}

                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Descripción / Justificación *
                                    </label>
                                    <textarea
                                        required
                                        rows={3}
                                        value={eventForm.event_description}
                                        onChange={(e) => setEventForm({ ...eventForm, event_description: e.target.value })}
                                        placeholder="Detalles del evento..."
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    />
                                </div>

                                <div className="flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsEventModalOpen(false)}
                                        className="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={savingEvent}
                                        className="rounded-lg bg-brand-primary px-4 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#082260]"
                                    >
                                        {savingEvent ? 'Guardando...' : 'Asentar Novedad'}
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
