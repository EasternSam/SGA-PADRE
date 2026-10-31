import React from 'react';
import { useForm, usePage, Head, Link } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { branding } = usePage().props;
    const form = useForm({
        email: '',
    });

    const primaryColor = branding?.primary_color || '#211170';
    const appName = branding?.name || 'Academic+';

    const submit = (e) => {
        e.preventDefault();
        form.post('/forgot-password');
    };

    return (
        <div className="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 bg-slate-950 font-sans relative overflow-hidden">
            <Head title={`Recuperar Contraseña - ${appName}`} />

            <div className="w-full max-w-md bg-white dark:bg-slate-900 rounded-[28px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] border border-slate-100 dark:border-slate-800 p-6 sm:p-8 relative z-10">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Recuperar Contraseña</h1>
                    
                </div>

                {status && (
                    <div className="mb-4 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800">
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label htmlFor="email" className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Correo Electrónico
                        </label>
                        <input 
                            id="email" 
                            type="email"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            required 
                            autoFocus 
                            placeholder="usuario@correo.com"
                            className="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-sm shadow-sm"
                        />
                        {form.errors.email && (
                            <p className="text-xs text-red-500 mt-1.5">{form.errors.email}</p>
                        )}
                    </div>

                    <div className="pt-2">
                        <button 
                            type="submit" 
                            disabled={form.processing}
                            className="w-full py-3.5 px-4 rounded-xl text-white font-bold text-sm tracking-wide shadow-md hover:shadow-lg transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center hover:brightness-105 disabled:opacity-50"
                            style={{ backgroundColor: primaryColor }}
                        >
                            {form.processing ? 'Enviando...' : 'Enviar Enlace de Recuperación'}
                        </button>
                    </div>

                    <div className="pt-3 text-center border-t border-slate-100 dark:border-slate-800">
                        <a href="/login" className="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                            ← Volver al Inicio de Sesión
                        </a>
                    </div>
                </form>
            </div>
        </div>
    );
}
