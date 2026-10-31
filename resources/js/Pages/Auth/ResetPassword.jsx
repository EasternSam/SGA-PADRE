import React from 'react';
import { useForm, usePage, Head } from '@inertiajs/react';

export default function ResetPassword({ token, email }) {
    const { branding } = usePage().props;
    const form = useForm({
        token: token,
        email: email || '',
        password: '',
        password_confirmation: '',
    });

    const primaryColor = branding?.primary_color || '#bb3902';
    const appName = branding?.name || 'Escuela Chavón';

    const submit = (e) => {
        e.preventDefault();
        form.post('/reset-password', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 bg-slate-950 font-sans relative overflow-hidden">
            <Head title={`Restablecer Contraseña - ${appName}`} />

            <div className="w-full max-w-md bg-white dark:bg-slate-900 rounded-[28px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] border border-slate-100 dark:border-slate-800 p-6 sm:p-8 relative z-10">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Restablecer Contraseña</h1>
                    
                </div>

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
                            className="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-sm shadow-sm"
                        />
                        {form.errors.email && (
                            <p className="text-xs text-red-500 mt-1.5">{form.errors.email}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="password" className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nueva Contraseña
                        </label>
                        <input 
                            id="password" 
                            type="password"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            required 
                            placeholder="••••••••"
                            className="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-sm shadow-sm"
                        />
                        {form.errors.password && (
                            <p className="text-xs text-red-500 mt-1.5">{form.errors.password}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="password_confirmation" className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Confirmar Nueva Contraseña
                        </label>
                        <input 
                            id="password_confirmation" 
                            type="password"
                            value={form.data.password_confirmation}
                            onChange={(e) => form.setData('password_confirmation', e.target.value)}
                            required 
                            placeholder="••••••••"
                            className="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-sm shadow-sm"
                        />
                        {form.errors.password_confirmation && (
                            <p className="text-xs text-red-500 mt-1.5">{form.errors.password_confirmation}</p>
                        )}
                    </div>

                    <div className="pt-2">
                        <button 
                            type="submit" 
                            disabled={form.processing}
                            className="w-full py-3.5 px-4 rounded-xl text-white font-bold text-sm tracking-wide shadow-md hover:shadow-lg transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center hover:brightness-105 disabled:opacity-50"
                            style={{ backgroundColor: primaryColor }}
                        >
                            {form.processing ? 'Guardando...' : 'Restablecer y Acceder'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
