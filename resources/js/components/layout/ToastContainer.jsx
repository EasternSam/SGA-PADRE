import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

export default function ToastContainer() {
    const { flash } = usePage().props;
    const [toasts, setToasts] = useState([]);

    useEffect(() => {
        if (flash?.success) {
            addToast('success', '¡Éxito!', flash.success);
        }
        if (flash?.error) {
            addToast('error', 'Atención', flash.error);
        }
        if (flash?.message) {
            addToast('info', 'Notificación', flash.message);
        }
    }, [flash]);

    const addToast = (type, title, message) => {
        const id = Date.now() + Math.random();
        setToasts(prev => [...prev, { id, type, title, message }]);
        setTimeout(() => {
            setToasts(prev => prev.filter(t => t.id !== id));
        }, 4500);
    };

    const removeToast = (id) => {
        setToasts(prev => prev.filter(t => t.id !== id));
    };

    if (toasts.length === 0) return null;

    return (
        <div 
            aria-live="assertive" 
            className="pointer-events-none fixed inset-0 z-[1500] flex items-end px-4 py-6 sm:items-start sm:p-6"
        >
            <div className="flex w-full flex-col items-center space-y-3 sm:items-end">
                {toasts.map((toast) => (
                    <div 
                        key={toast.id}
                        className={`pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl bg-white dark:bg-[#161622] shadow-2xl ring-1 ring-black/5 dark:ring-white/10 border-l-4 p-4 transition-all animate-in slide-in-from-top-2 duration-200 ${
                            toast.type === 'success' ? 'border-green-500' : toast.type === 'error' ? 'border-red-500' : 'border-blue-500'
                        }`}
                    >
                        <div className="flex items-start">
                            <div className="flex-shrink-0 pt-0.5">
                                {toast.type === 'success' && (
                                    <svg className="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
                                    </svg>
                                )}
                                {toast.type === 'error' && (
                                    <svg className="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clipRule="evenodd" />
                                    </svg>
                                )}
                                {toast.type === 'info' && (
                                    <svg className="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.75.75 0 01.747.75v3.25a.75.75 0 001.5 0v-4A.75.75 0 0010.75 9H9z" clipRule="evenodd" />
                                    </svg>
                                )}
                            </div>
                            <div className="ml-3 w-0 flex-1">
                                <p className="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">{toast.title}</p>
                                <p className="mt-0.5 text-xs text-gray-600 dark:text-gray-300">{toast.message}</p>
                            </div>
                            <div className="ml-3 flex flex-shrink-0">
                                <button
                                    type="button"
                                    onClick={() => removeToast(toast.id)}
                                    className="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                                >
                                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
