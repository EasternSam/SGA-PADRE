import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ users = [], roles = [], filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [activeRole, setActiveRole] = useState(filters.role || 'all');
    const [editingUser, setEditingUser] = useState(null);
    const [isCreateOpen, setIsCreateOpen] = useState(false);

    const { data, setData, post, put, delete: destroy, processing, reset, errors } = useForm({
        name: '',
        email: '',
        password: '',
        role: roles[0] || 'Admin',
        kiosk_pin: '',
    });

    const handleSearch = (e) => {
        e?.preventDefault();
        router.get('/admin/users', { search, role: activeRole }, { preserveState: true, preserveScroll: true });
    };

    const handleRoleFilter = (r) => {
        setActiveRole(r);
        router.get('/admin/users', { search, role: r }, { preserveState: true, preserveScroll: true });
    };

    const openCreateModal = () => {
        setEditingUser(null);
        reset();
        setData({
            name: '',
            email: '',
            password: '',
            role: roles[0] || 'Admin',
            kiosk_pin: '',
        });
        setIsCreateOpen(true);
    };

    const openEditModal = (user) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            password: '',
            role: user.roles[0] || 'Admin',
            kiosk_pin: '',
        });
        setIsCreateOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingUser) {
            put(`/admin/users/${editingUser.id}`, {
                onSuccess: () => {
                    setIsCreateOpen(false);
                    setEditingUser(null);
                    reset();
                }
            });
        } else {
            post('/admin/users', {
                onSuccess: () => {
                    setIsCreateOpen(false);
                    reset();
                }
            });
        }
    };

    const handleDelete = (user) => {
        if (confirm(`¿Estás seguro de eliminar al usuario ${user.name}? Esta acción no se puede deshacer.`)) {
            destroy(`/admin/users/${user.id}`);
        }
    };

    const getRoleBadge = (roleName) => {
        switch (roleName) {
            case 'Admin':
                return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20';
            case 'Contabilidad':
                return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
            case 'Caja':
                return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
            case 'Registro':
                return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20';
            case 'Profesor':
                return 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20';
            default:
                return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20';
        }
    };

    return (
        <AuthenticatedLayout title="Gestión de Usuarios">
            <Head title="Usuarios del Sistema - SGA" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-sga-primary dark:text-blue-400 uppercase tracking-wider">
                            <i className="fa-solid fa-users-gear"></i> Seguridad & Privilegios
                        </div>
                        <h1 className="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                            Administración de Cuentas de Acceso
                        </h1>
                        
                    </div>

                    <button
                        onClick={openCreateModal}
                        className="px-5 py-2.5 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 shrink-0"
                    >
                        <i className="fa-solid fa-user-plus"></i>
                        <span>Nuevo Usuario</span>
                    </button>
                </div>

                {/* Filter & Search Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <form onSubmit={handleSearch} className="relative w-full md:w-80">
                        <i className="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Buscar por nombre o correo..."
                            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                        />
                    </form>

                    <div className="flex gap-1.5 overflow-x-auto w-full md:w-auto pb-1 scrollbar-none">
                        <button
                            onClick={() => handleRoleFilter('all')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-bold border transition-all ${
                                activeRole === 'all'
                                    ? 'bg-sga-primary text-white border-blue-600 shadow-sm'
                                    : 'bg-slate-50 dark:bg-white/5 border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-400 hover:text-white'
                            }`}
                        >
                            Todos
                        </button>
                        {roles.map(r => (
                            <button
                                key={r}
                                onClick={() => handleRoleFilter(r)}
                                className={`px-3 py-1.5 rounded-lg text-xs font-bold border transition-all ${
                                    activeRole === r
                                        ? 'bg-sga-primary text-white border-blue-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-white/5 border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-400 hover:text-white'
                                }`}
                            >
                                {r}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Users Table */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 dark:bg-white/[0.02] border-b border-slate-200 dark:border-white/5 text-xs uppercase font-bold text-slate-500 tracking-wider">
                                <tr>
                                    <th className="px-6 py-4">Usuario</th>
                                    <th className="px-6 py-4">Correo Electrónico</th>
                                    <th className="px-6 py-4">Roles Asignados</th>
                                    <th className="px-6 py-4 text-center">PIN Kiosco</th>
                                    <th className="px-6 py-4">Fecha Creación</th>
                                    <th className="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-white/5 font-medium">
                                {users.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-slate-400">
                                            No se encontraron usuarios coincidentes.
                                        </td>
                                    </tr>
                                ) : (
                                    users.map(u => (
                                        <tr key={u.id} className="hover:bg-slate-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-9 h-9 rounded-xl bg-sga-primary/10 text-sga-primary dark:text-blue-400 font-bold text-xs flex items-center justify-center border border-sga-primary/20">
                                                        {u.name.substring(0, 2).toUpperCase()}
                                                    </div>
                                                    <span className="font-bold text-slate-800 dark:text-white">{u.name}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600 dark:text-slate-300 font-mono text-xs">{u.email}</td>
                                            <td className="px-6 py-4">
                                                <div className="flex flex-wrap gap-1">
                                                    {u.roles.map(r => (
                                                        <span key={r} className={`px-2.5 py-0.5 rounded-full text-xs font-bold border ${getRoleBadge(r)}`}>
                                                            {r}
                                                        </span>
                                                    ))}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                {u.has_kiosk_pin ? (
                                                    <span className="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        <i className="fa-solid fa-lock"></i> Activo
                                                    </span>
                                                ) : (
                                                    <span className="text-xs text-slate-400">Sin PIN</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-xs text-slate-500 font-mono">{u.created_at}</td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        onClick={() => openEditModal(u)}
                                                        className="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all"
                                                        title="Editar Usuario"
                                                    >
                                                        <i className="fa-solid fa-pen-to-square text-xs"></i>
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(u)}
                                                        className="w-8 h-8 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center transition-all"
                                                        title="Eliminar Usuario"
                                                    >
                                                        <i className="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Create / Edit Modal */}
                {isCreateOpen && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                        <div className="bg-white dark:bg-[#161622] border border-slate-200 dark:border-white/10 rounded-2xl max-w-lg w-full p-6 shadow-2xl">
                            <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/5">
                                <h3 className="text-lg font-bold text-slate-800 dark:text-white">
                                    {editingUser ? 'Modificar Usuario' : 'Registrar Nuevo Usuario'}
                                </h3>
                                <button
                                    onClick={() => setIsCreateOpen(false)}
                                    className="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-white flex items-center justify-center"
                                >
                                    &times;
                                </button>
                            </div>

                            <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                        Nombre Completo
                                    </label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                        className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                        Correo Electrónico
                                    </label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={e => setData('email', e.target.value)}
                                        className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                        Contraseña {editingUser && <span className="text-slate-400 normal-case">(dejar vacío para mantener)</span>}
                                    </label>
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                        placeholder={editingUser ? '••••••••' : 'Mínimo 6 caracteres'}
                                        className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                        required={!editingUser}
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                        Rol del Sistema
                                    </label>
                                    <select
                                        value={data.role}
                                        onChange={e => setData('role', e.target.value)}
                                        className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    >
                                        {roles.map(r => (
                                            <option key={r} value={r} className="text-black">
                                                {r}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                        PIN Kiosco / Terminal (4 a 6 dígitos)
                                    </label>
                                    <input
                                        type="password"
                                        maxLength="6"
                                        value={data.kiosk_pin}
                                        onChange={e => setData('kiosk_pin', e.target.value)}
                                        placeholder="Opcional para autoservicio"
                                        className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    />
                                </div>

                                <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-white/5">
                                    <button
                                        type="button"
                                        onClick={() => setIsCreateOpen(false)}
                                        className="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-white"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="px-6 py-2.5 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2"
                                    >
                                        {processing ? (
                                            <>
                                                <i className="fa-solid fa-circle-notch fa-spin"></i>
                                                <span>Guardando...</span>
                                            </>
                                        ) : (
                                            <>
                                                <i className="fa-solid fa-check"></i>
                                                <span>{editingUser ? 'Guardar Cambios' : 'Crear Usuario'}</span>
                                            </>
                                        )}
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
