import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Users,
    UserPlus,
    Search,
    Filter,
    Briefcase,
    Building2,
    DollarSign,
    Fingerprint,
    Calendar,
    ChevronRight,
    Edit2,
    CheckCircle2,
    XCircle,
    X,
    Clock,
    CreditCard,
    Shield
} from 'lucide-react';

export default function EmployeesIndex({
    auth,
    employees,
    filters = {},
    stats = {},
    departments = [],
    usersAvailable = [],
    roles = []
}) {
    const [search, setSearch] = useState(filters.search || '');
    const [department, setDepartment] = useState(filters.department || '');
    const [status, setStatus] = useState(filters.status || '');
    const [contractType, setContractType] = useState(filters.contract_type || '');

    // Modal state
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingEmployee, setEditingEmployee] = useState(null);
    const [isNewUser, setIsNewUser] = useState(false);
    const [formData, setFormData] = useState({
        is_new_user: false,
        user_id: '',
        new_user_name: '',
        new_user_email: '',
        new_user_password: '',
        role_name: roles[0] || 'Profesor',
        biometric_id: '',
        position: '',
        department: '',
        contract_type: 'Mensual',
        base_salary: '',
        hourly_rate: '',
        hire_date: '',
        status: 'Activo',
    });
    const [processing, setProcessing] = useState(false);

    const handleFilter = (e) => {
        if (e) e.preventDefault();
        router.get(route('admin.hr.employees'), {
            search,
            department,
            status,
            contract_type: contractType,
        }, { preserveState: true, preserveScroll: true });
    };

    const handleResetFilters = () => {
        setSearch('');
        setDepartment('');
        setStatus('');
        setContractType('');
        router.get(route('admin.hr.employees'), {}, { preserveScroll: true });
    };

    const openCreateModal = () => {
        setEditingEmployee(null);
        setIsNewUser(false);
        setFormData({
            is_new_user: false,
            user_id: usersAvailable[0]?.id || '',
            new_user_name: '',
            new_user_email: '',
            new_user_password: '',
            role_name: roles[0] || 'Profesor',
            biometric_id: '',
            position: '',
            department: '',
            contract_type: 'Mensual',
            base_salary: '',
            hourly_rate: '',
            hire_date: new Date().toISOString().split('T')[0],
            status: 'Activo',
        });
        setIsModalOpen(true);
    };

    const openEditModal = (emp) => {
        setEditingEmployee(emp);
        setIsNewUser(false);
        setFormData({
            is_new_user: false,
            user_id: emp.user_id,
            new_user_name: emp.user?.name || '',
            new_user_email: emp.user?.email || '',
            new_user_password: '',
            role_name: emp.user?.roles?.[0]?.name || roles[0] || 'Profesor',
            biometric_id: emp.biometric_id || '',
            position: emp.position || '',
            department: emp.department || '',
            contract_type: emp.contract_type || 'Mensual',
            base_salary: emp.base_salary || '',
            hourly_rate: emp.hourly_rate || '',
            hire_date: emp.hire_date ? emp.hire_date.substring(0, 10) : '',
            status: emp.status || 'Activo',
        });
        setIsModalOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setProcessing(true);

        const payload = {
            ...formData,
            is_new_user: isNewUser,
        };

        if (editingEmployee) {
            router.put(route('admin.hr.employees.update', editingEmployee.id), payload, {
                onSuccess: () => {
                    setIsModalOpen(false);
                    setProcessing(false);
                },
                onError: () => setProcessing(false)
            });
        } else {
            router.post(route('admin.hr.employees.store'), payload, {
                onSuccess: () => {
                    setIsModalOpen(false);
                    setProcessing(false);
                },
                onError: () => setProcessing(false)
            });
        }
    };

    const formatCurrency = (val) => {
        return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(val || 0);
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Directorio de Recursos Humanos" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="inline-flex items-center gap-1.5 rounded-md bg-brand-primary/10 px-2.5 py-1 text-xs font-semibold text-brand-primary dark:bg-blue-900/30 dark:text-blue-300">
                                <Briefcase className="h-3.5 w-3.5" />
                                Gestión del Talento & Nómina
                            </span>
                        </div>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Directorio de Colaboradores
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <Link
                            href={route('admin.hr.attendances')}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Clock className="h-3.5 w-3.5 text-slate-500" />
                            Reloj Biométrico
                        </Link>

                        <Link
                            href={route('admin.hr.payroll')}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <CreditCard className="h-3.5 w-3.5 text-slate-500" />
                            Gestión de Nómina
                        </Link>

                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="inline-flex items-center gap-2 rounded-lg bg-brand-primary px-4 py-2 text-xs font-bold text-white shadow-md hover:bg-[#082260] active:scale-95 transition"
                        >
                            <UserPlus className="h-4 w-4" />
                            Nuevo Colaborador
                        </button>
                    </div>
                </div>

                {/* Key Metrics Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Total Colaboradores</span>
                            <Users className="h-4 w-4" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-slate-900 dark:text-white">
                                {stats.total || 0}
                            </span>
                            <span className="text-xs font-medium text-slate-500">en nómina</span>
                        </div>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Personal Activo</span>
                            <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                                {stats.active || 0}
                            </span>
                            <span className="text-xs font-medium text-slate-500">colaboradores</span>
                        </div>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Personal Inactivo</span>
                            <XCircle className="h-4 w-4 text-rose-500" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-rose-600 dark:text-rose-400">
                                {stats.inactive || 0}
                            </span>
                            <span className="text-xs font-medium text-slate-500">desvinculados</span>
                        </div>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Masa Salarial Mensual</span>
                            <DollarSign className="h-4 w-4 text-blue-600" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-2xl font-black text-slate-900 dark:text-white">
                                {formatCurrency(stats.monthly_payroll_estimate)}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Filter Bar */}
                <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <form onSubmit={handleFilter} className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input
                                type="text"
                                placeholder="Buscar nombre, correo, ID..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>

                        <div>
                            <select
                                value={department}
                                onChange={(e) => setDepartment(e.target.value)}
                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">Todos los Departamentos</option>
                                {departments.map(d => (
                                    <option key={d} value={d}>{d}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <select
                                value={contractType}
                                onChange={(e) => setContractType(e.target.value)}
                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">Todos los Contratos</option>
                                <option value="Mensual">Mensual</option>
                                <option value="Por Horas">Por Horas</option>
                            </select>
                        </div>

                        <div>
                            <select
                                value={status}
                                onChange={(e) => setStatus(e.target.value)}
                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">Todos los Estados</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>

                        <div className="flex items-center gap-2">
                            <button
                                type="submit"
                                className="w-full rounded-lg bg-brand-primary py-2 text-xs font-bold text-white shadow-sm hover:bg-[#082260] transition"
                            >
                                Filtrar
                            </button>
                            <button
                                type="button"
                                onClick={handleResetFilters}
                                className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                {/* Employees Data Table */}
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                <tr>
                                    <th className="px-5 py-3.5">Colaborador</th>
                                    <th className="px-5 py-3.5">Cargo / Depto</th>
                                    <th className="px-5 py-3.5 text-center">Rol Sistema</th>
                                    <th className="px-5 py-3.5 text-center">Biométrico</th>
                                    <th className="px-5 py-3.5">Contrato & Salario</th>
                                    <th className="px-5 py-3.5 text-center">Estado</th>
                                    <th className="px-5 py-3.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {employees.data?.map(emp => (
                                    <tr key={emp.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                        <td className="px-5 py-3.5">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-primary/10 font-bold text-brand-primary dark:bg-blue-900/40 dark:text-blue-300">
                                                    {emp.user?.name?.substring(0, 2).toUpperCase() || 'EM'}
                                                </div>
                                                <div>
                                                    <Link
                                                        href={route('admin.hr.employees.profile', emp.id)}
                                                        className="font-bold text-slate-900 hover:text-brand-primary dark:text-white dark:hover:text-blue-400"
                                                    >
                                                        {emp.user?.name || 'Sin Usuario'}
                                                    </Link>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                                        {emp.user?.email}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <div className="font-semibold text-slate-800 dark:text-slate-200">
                                                {emp.position || 'Sin cargo'}
                                            </div>
                                            <div className="text-xs text-slate-500">
                                                {emp.department || 'General'}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3.5 text-center">
                                            <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                <Shield className="h-3 w-3 text-slate-500" />
                                                {emp.user?.roles?.[0]?.name || 'Sin Rol'}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3.5 text-center">
                                            {emp.biometric_id ? (
                                                <span className="inline-flex items-center gap-1 font-mono text-xs font-bold text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded">
                                                    <Fingerprint className="h-3 w-3" />
                                                    #{emp.biometric_id}
                                                </span>
                                            ) : (
                                                <span className="text-xs text-slate-400">—</span>
                                            )}
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <div className="font-mono text-xs font-bold text-slate-900 dark:text-white">
                                                {emp.contract_type === 'Mensual'
                                                    ? formatCurrency(emp.base_salary)
                                                    : `${formatCurrency(emp.hourly_rate)} / hr`
                                                }
                                            </div>
                                            <div className="text-[11px] text-slate-500">
                                                {emp.contract_type}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3.5 text-center">
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                                emp.status === 'Activo'
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                    : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                                            }`}>
                                                {emp.status}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3.5 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    onClick={() => openEditModal(emp)}
                                                    className="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                                    title="Editar Parámetros"
                                                >
                                                    <Edit2 className="h-4 w-4" />
                                                </button>
                                                <Link
                                                    href={route('admin.hr.employees.profile', emp.id)}
                                                    className="inline-flex items-center gap-1 rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                                >
                                                    Expediente
                                                    <ChevronRight className="h-3.5 w-3.5" />
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {employees.data?.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="py-12 text-center text-sm text-slate-500">
                                            No se encontraron colaboradores registrados con los filtros aplicados.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Create/Edit Modal */}
                {isModalOpen && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 max-h-[90vh] overflow-y-auto">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                                <div>
                                    <h3 className="text-lg font-black text-slate-900 dark:text-white">
                                        {editingEmployee ? 'Editar Colaborador' : 'Registrar Nuevo Colaborador'}
                                    </h3>
                                    <p className="text-xs text-slate-500">
                                        {editingEmployee ? `Modificando expediente de ${editingEmployee.user?.name}` : 'Crea un usuario o vincula uno existente y asigna sus parámetros salariales'}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsModalOpen(false)}
                                    className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                                {!editingEmployee && (
                                    <div className="flex items-center gap-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                        <label className="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="user_mode"
                                                checked={!isNewUser}
                                                onChange={() => setIsNewUser(false)}
                                                className="text-brand-primary focus:ring-brand-primary"
                                            />
                                            Vincular Usuario Existente
                                        </label>
                                        <label className="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="user_mode"
                                                checked={isNewUser}
                                                onChange={() => setIsNewUser(true)}
                                                className="text-brand-primary focus:ring-brand-primary"
                                            />
                                            Crear Nuevo Usuario
                                        </label>
                                    </div>
                                )}

                                {!editingEmployee && !isNewUser && (
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Seleccionar Usuario del Sistema *
                                        </label>
                                        <select
                                            value={formData.user_id}
                                            onChange={(e) => setFormData({ ...formData, user_id: e.target.value })}
                                            required
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <option value="">Seleccione un usuario...</option>
                                            {usersAvailable.map(u => (
                                                <option key={u.id} value={u.id}>
                                                    {u.name} ({u.email})
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}

                                {!editingEmployee && isNewUser && (
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Nombre Completo *
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                value={formData.new_user_name}
                                                onChange={(e) => setFormData({ ...formData, new_user_name: e.target.value })}
                                                placeholder="Ej. Juan Pérez"
                                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Correo Institucional *
                                            </label>
                                            <input
                                                type="email"
                                                required
                                                value={formData.new_user_email}
                                                onChange={(e) => setFormData({ ...formData, new_user_email: e.target.value })}
                                                placeholder="colaborador@itla.edu.do"
                                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Contraseña Inicial *
                                            </label>
                                            <input
                                                type="password"
                                                required
                                                value={formData.new_user_password}
                                                onChange={(e) => setFormData({ ...formData, new_user_password: e.target.value })}
                                                placeholder="Mínimo 8 caracteres"
                                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            />
                                        </div>
                                    </div>
                                )}

                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Rol de Acceso en SGA *
                                        </label>
                                        <select
                                            value={formData.role_name}
                                            onChange={(e) => setFormData({ ...formData, role_name: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            {roles.map(r => (
                                                <option key={r} value={r}>{r}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            ID Biométrico (Reloj)
                                        </label>
                                        <input
                                            type="number"
                                            value={formData.biometric_id}
                                            onChange={(e) => setFormData({ ...formData, biometric_id: e.target.value })}
                                            placeholder="Ej. 104"
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Cargo / Puesto
                                        </label>
                                        <input
                                            type="text"
                                            value={formData.position}
                                            onChange={(e) => setFormData({ ...formData, position: e.target.value })}
                                            placeholder="Ej. Docente de Programación"
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Departamento
                                        </label>
                                        <input
                                            type="text"
                                            value={formData.department}
                                            onChange={(e) => setFormData({ ...formData, department: e.target.value })}
                                            placeholder="Ej. Académico, Finanzas, TI..."
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Tipo de Contrato *
                                        </label>
                                        <select
                                            value={formData.contract_type}
                                            onChange={(e) => setFormData({ ...formData, contract_type: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <option value="Mensual">Mensual</option>
                                            <option value="Por Horas">Por Horas</option>
                                        </select>
                                    </div>

                                    {formData.contract_type === 'Mensual' ? (
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Salario Base (DOP)
                                            </label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                value={formData.base_salary}
                                                onChange={(e) => setFormData({ ...formData, base_salary: e.target.value })}
                                                placeholder="0.00"
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
                                                value={formData.hourly_rate}
                                                onChange={(e) => setFormData({ ...formData, hourly_rate: e.target.value })}
                                                placeholder="0.00"
                                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            />
                                        </div>
                                    )}

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Estado del Empleado *
                                        </label>
                                        <select
                                            value={formData.status}
                                            onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                                            className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <option value="Activo">Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div className="flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsModalOpen(false)}
                                        className="rounded-lg px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-brand-primary px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-[#082260] disabled:opacity-50 transition"
                                    >
                                        {processing ? 'Guardando...' : (editingEmployee ? 'Actualizar Expediente' : 'Crear Colaborador')}
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
