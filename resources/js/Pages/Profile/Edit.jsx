import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { useForm, usePage, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ mustVerifyEmail, status, user }) {
    const { auth } = usePage().props;
    const currentUser = user || auth?.user || {};

    // --- FORM 1: INFORMACIÓN DEL PERFIL ---
    const profileForm = useForm({
        name: currentUser.name || '',
        email: currentUser.email || '',
        photo: null,
    });

    const [photoPreview, setPhotoPreview] = useState(currentUser.profile_photo_url || null);

    const handlePhotoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            profileForm.setData('photo', file);
            setPhotoPreview(URL.createObjectURL(file));
        }
    };

    const submitProfile = (e) => {
        e.preventDefault();
        profileForm.post('/profile', {
            _method: 'patch',
            preserveScroll: true,
            forceFormData: true,
        });
    };

    // --- FORM 2: CAMBIO DE CONTRASEÑA ---
    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const [showCurrentPass, setShowCurrentPass] = useState(false);
    const [showNewPass, setShowNewPass] = useState(false);

    const submitPassword = (e) => {
        e.preventDefault();
        passwordForm.put('/password', {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    // --- MODAL: ELIMINAR CUENTA ---
    const [confirmingDeletion, setConfirmingDeletion] = useState(false);
    const deleteForm = useForm({
        password: '',
    });

    const submitDelete = (e) => {
        e.preventDefault();
        deleteForm.delete('/profile', {
            preserveScroll: true,
            onSuccess: () => setConfirmingDeletion(false),
        });
    };

    return (
        <AuthenticatedLayout title="Mi Perfil">
            <div className="space-y-8 max-w-4xl mx-auto py-2">
                {/* Encabezado Institucional */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-white/10">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                            <a href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</a>
                            <span>/</span>
                            <span className="text-gray-600 dark:text-gray-300">Perfil de Usuario</span>
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Mi Perfil Institucional
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-brand-primary dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                            <span className="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            Rol: {auth?.user?.roles?.[0] || 'Administrador'}
                        </span>
                    </div>
                </div>

                {/* Banner de Estado / Notificación */}
                {status === 'profile-updated' && (
                    <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center gap-2">
                        <svg className="w-4 h-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
                        </svg>
                        <span>La información de su perfil se actualizó correctamente.</span>
                    </div>
                )}
                {status === 'password-updated' && (
                    <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center gap-2">
                        <svg className="w-4 h-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
                        </svg>
                        <span>Su contraseña de acceso ha sido actualizada de forma segura.</span>
                    </div>
                )}

                {/* TARJETA 1: INFORMACIÓN DEL PERFIL */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl ring-1 ring-gray-900/5 dark:ring-white/10 p-6 sm:p-8 border border-gray-100 dark:border-gray-800 shadow-sm">
                    <div className="max-w-xl">
                        <header className="mb-6">
                            <h2 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg className="w-5 h-5 text-brand-primary dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                                </svg>
                                Información del Perfil
                            </h2>
                            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Actualice su nombre legal, correo electrónico institucional y foto de perfil.
                            </p>
                        </header>

                        <form onSubmit={submitProfile} className="space-y-5">
                            {/* Fotografía de Perfil */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Foto de Perfil
                                </label>
                                <div className="flex items-center gap-4">
                                    <div className="relative">
                                        {photoPreview ? (
                                            <img 
                                                src={photoPreview} 
                                                alt={currentUser.name} 
                                                className="w-16 h-16 rounded-full object-cover ring-2 ring-brand-primary/20 shadow"
                                            />
                                        ) : (
                                            <div className="w-16 h-16 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-xl ring-2 ring-brand-primary/20 shadow">
                                                {currentUser.name?.charAt(0) || 'U'}
                                            </div>
                                        )}
                                    </div>
                                    <div>
                                        <input
                                            id="photo"
                                            type="file"
                                            accept="image/*"
                                            onChange={handlePhotoChange}
                                            className="block w-full text-xs text-gray-500 dark:text-gray-400
                                                file:mr-3 file:py-1.5 file:px-3.5
                                                file:rounded-xl file:border-0
                                                file:text-xs file:font-semibold
                                                file:bg-brand-primary/10 file:text-brand-primary dark:file:bg-blue-900/30 dark:file:text-blue-300
                                                hover:file:bg-brand-primary/20 cursor-pointer"
                                        />
                                        <p className="text-[11px] text-gray-400 mt-1">PNG, JPG o WEBP hasta 2MB</p>
                                    </div>
                                </div>
                                {profileForm.errors.photo && (
                                    <p className="text-xs text-red-500 mt-1.5">{profileForm.errors.photo}</p>
                                )}
                            </div>

                            {/* Nombre */}
                            <div>
                                <label htmlFor="name" className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre Completo
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    value={profileForm.data.name}
                                    onChange={(e) => profileForm.setData('name', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-0 py-2.5 px-3.5 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-brand-primary text-xs bg-gray-50 dark:bg-gray-900/50"
                                />
                                {profileForm.errors.name && (
                                    <p className="text-xs text-red-500 mt-1.5">{profileForm.errors.name}</p>
                                )}
                            </div>

                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Correo Electrónico
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={profileForm.data.email}
                                    onChange={(e) => profileForm.setData('email', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-0 py-2.5 px-3.5 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-brand-primary text-xs bg-gray-50 dark:bg-gray-900/50"
                                />
                                {profileForm.errors.email && (
                                    <p className="text-xs text-red-500 mt-1.5">{profileForm.errors.email}</p>
                                )}
                            </div>

                            <div className="pt-2 flex items-center gap-3">
                                <button
                                    type="submit"
                                    disabled={profileForm.processing}
                                    className="bg-brand-primary hover:bg-brand-dark text-white font-semibold py-2.5 px-5 rounded-xl shadow-sm transition active:scale-[0.98] text-xs flex items-center gap-2 disabled:opacity-50"
                                >
                                    {profileForm.processing && (
                                        <svg className="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    )}
                                    Guardar Cambios
                                </button>
                                {profileForm.recentlySuccessful && (
                                    <span className="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                        ¡Guardado con éxito!
                                    </span>
                                )}
                            </div>
                        </form>
                    </div>
                </div>

                {/* TARJETA 2: ACTUALIZAR CONTRASEÑA */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl ring-1 ring-gray-900/5 dark:ring-white/10 p-6 sm:p-8 border border-gray-100 dark:border-gray-800 shadow-sm">
                    <div className="max-w-xl">
                        <header className="mb-6">
                            <h2 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg className="w-5 h-5 text-brand-primary dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clipRule="evenodd" />
                                </svg>
                                Seguridad y Contraseña
                            </h2>
                            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Asegúrese de que su cuenta utilice una contraseña robusta para mantener la integridad de sus accesos.
                            </p>
                        </header>

                        <form onSubmit={submitPassword} className="space-y-5">
                            {/* Contraseña Actual */}
                            <div>
                                <label htmlFor="current_password" className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Contraseña Actual
                                </label>
                                <div className="relative">
                                    <input
                                        id="current_password"
                                        type={showCurrentPass ? 'text' : 'password'}
                                        value={passwordForm.data.current_password}
                                        onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 pl-3.5 pr-10 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-brand-primary text-xs bg-gray-50 dark:bg-gray-900/50"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowCurrentPass(!showCurrentPass)}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                {passwordForm.errors.current_password && (
                                    <p className="text-xs text-red-500 mt-1.5">{passwordForm.errors.current_password}</p>
                                )}
                            </div>

                            {/* Nueva Contraseña */}
                            <div>
                                <label htmlFor="password" className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Nueva Contraseña
                                </label>
                                <div className="relative">
                                    <input
                                        id="password"
                                        type={showNewPass ? 'text' : 'password'}
                                        value={passwordForm.data.password}
                                        onChange={(e) => passwordForm.setData('password', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 pl-3.5 pr-10 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-brand-primary text-xs bg-gray-50 dark:bg-gray-900/50"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowNewPass(!showNewPass)}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                {passwordForm.errors.password && (
                                    <p className="text-xs text-red-500 mt-1.5">{passwordForm.errors.password}</p>
                                )}
                            </div>

                            {/* Confirmación */}
                            <div>
                                <label htmlFor="password_confirmation" className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Confirmar Nueva Contraseña
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                    className="w-full rounded-xl border-0 py-2.5 px-3.5 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-brand-primary text-xs bg-gray-50 dark:bg-gray-900/50"
                                />
                                {passwordForm.errors.password_confirmation && (
                                    <p className="text-xs text-red-500 mt-1.5">{passwordForm.errors.password_confirmation}</p>
                                )}
                            </div>

                            <div className="pt-2 flex items-center gap-3">
                                <button
                                    type="submit"
                                    disabled={passwordForm.processing}
                                    className="bg-brand-primary hover:bg-brand-dark text-white font-semibold py-2.5 px-5 rounded-xl shadow-sm transition active:scale-[0.98] text-xs flex items-center gap-2 disabled:opacity-50"
                                >
                                    {passwordForm.processing && (
                                        <svg className="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    )}
                                    Actualizar Contraseña
                                </button>
                                {passwordForm.recentlySuccessful && (
                                    <span className="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                        ¡Contraseña guardada!
                                    </span>
                                )}
                            </div>
                        </form>
                    </div>
                </div>

                {/* TARJETA 3: ZONA DE PELIGRO (ELIMINAR CUENTA) */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl ring-1 ring-red-500/20 p-6 sm:p-8 border border-red-100 dark:border-red-950/40 shadow-sm">
                    <div className="max-w-xl">
                        <header className="mb-4">
                            <h2 className="text-base font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                                <svg className="w-5 h-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clipRule="evenodd" />
                                </svg>
                                Zona de Peligro
                            </h2>
                            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Una vez eliminada su cuenta, todos los recursos y datos asociados serán permanentemente destruidos.
                            </p>
                        </header>

                        <button
                            type="button"
                            onClick={() => setConfirmingDeletion(true)}
                            className="bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 font-semibold py-2.5 px-4 rounded-xl border border-red-200 dark:border-red-800 text-xs transition active:scale-[0.98]"
                        >
                            Solicitar Eliminación de Cuenta
                        </button>

                        {/* Modal de Confirmación */}
                        {confirmingDeletion && (
                            createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/70 backdrop-blur-sm animate-in fade-in">
                                <div className="bg-white dark:bg-[#161622] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-800">
                                    <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                        ¿Está seguro de que desea eliminar su cuenta?
                                    </h3>
                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        Por favor, ingrese su contraseña actual para confirmar esta acción irreversible.
                                    </p>

                                    <form onSubmit={submitDelete} className="mt-4 space-y-4">
                                        <div>
                                            <input
                                                type="password"
                                                placeholder="Contraseña de confirmación"
                                                value={deleteForm.data.password}
                                                onChange={(e) => deleteForm.setData('password', e.target.value)}
                                                className="w-full rounded-xl border-0 py-2.5 px-3.5 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 text-xs"
                                                autoFocus
                                            />
                                            {deleteForm.errors.password && (
                                                <p className="text-xs text-red-500 mt-1">{deleteForm.errors.password}</p>
                                            )}
                                        </div>

                                        <div className="flex justify-end gap-2.5 pt-2">
                                            <button
                                                type="button"
                                                onClick={() => setConfirmingDeletion(false)}
                                                className="py-2 px-4 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={deleteForm.processing}
                                                className="py-2 px-4 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-700 text-white shadow-sm"
                                            >
                                                Sí, Eliminar Cuenta
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>, document.body)
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
