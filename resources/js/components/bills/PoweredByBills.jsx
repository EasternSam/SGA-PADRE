import React from 'react';
import { ShieldCheck, Zap } from 'lucide-react';

/**
 * PoweredByBills — Sello oficial del motor transaccional y tributario incrustado Gridbase Bills Core.
 *
 * Variants:
 * - 'sidebar': Compacto para la sección FINANZAS del menú lateral
 * - 'badge': Pill elegante para cabeceras de módulos financieros y POS
 * - 'banner': Barra informativa completa con estado del motor e-CF / DGII
 * - 'receipt': Sello fiscal compacto para el panel de ticket y recibos POS
 * - 'footer': Pie de página de módulos contables y tributarios
 */
export default function PoweredByBills({
    variant = 'badge',
    version = 'v2.4 e-CF',
    className = '',
}) {
    if (variant === 'sidebar') {
        return (
            <div
                className={`mx-3 mb-2 px-2.5 py-1.5 rounded-lg bg-gradient-to-r from-indigo-500/15 via-violet-500/15 to-emerald-500/10 border border-indigo-400/20 flex items-center justify-between gap-2 select-none ${className}`}
                title="Motor Transaccional, Contable y Tributario DGII — Powered by Gridbase Bills"
            >
                <div className="flex items-center gap-1.5 min-w-0">
                    <span className="w-4 h-4 rounded-md bg-indigo-500/25 border border-indigo-400/40 flex items-center justify-center shrink-0">
                        <Zap className="w-2.5 h-2.5 text-indigo-300 fill-indigo-300" />
                    </span>
                    <span className="text-[10px] font-semibold tracking-tight text-indigo-100/90 truncate">
                        Powered by <strong className="font-extrabold text-white">Gridbase Bills</strong>
                    </span>
                </div>
                <span className="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-400/25 shrink-0">
                    e-CF
                </span>
            </div>
        );
    }

    if (variant === 'receipt') {
        return (
            <div
                className={`flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/70 text-[11px] text-slate-600 dark:text-slate-300 ${className}`}
            >
                <ShieldCheck className="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0" />
                <span>
                    Powered by <strong className="font-bold text-slate-900 dark:text-white">Gridbase Bills</strong>
                </span>
                <span className="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border border-indigo-500/20">
                    DGII Core
                </span>
            </div>
        );
    }

    if (variant === 'banner') {
        return (
            <div
                className={`flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white border border-indigo-500/30 shadow-sm ${className}`}
            >
                <div className="flex items-center gap-2.5">
                    <div className="w-7 h-7 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center shrink-0">
                        <Zap className="w-4 h-4 text-indigo-300 fill-indigo-300" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-semibold text-indigo-100">
                                Powered by <strong className="font-extrabold text-white">Gridbase Bills</strong>
                            </span>
                            <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                Core Embebido {version}
                            </span>
                        </div>
                        <p className="text-[11px] text-slate-300/80">
                            Motor transaccional unificado: Firma digital XMLDSig RSA-SHA256, secuencias e-CF, Pre-validador DGII (606/607/608) y Partida Doble.
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-2 text-[11px] text-indigo-200 font-medium">
                    <ShieldCheck className="w-4 h-4 text-emerald-400" />
                    <span>Norma General DGII 06-2018 & 01-2020</span>
                </div>
            </div>
        );
    }

    if (variant === 'footer') {
        return (
            <div
                className={`mt-6 pt-4 border-t border-slate-200/70 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400 ${className}`}
            >
                <div className="flex items-center gap-1.5">
                    <Zap className="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 fill-indigo-600 dark:fill-indigo-400" />
                    <span>
                        Powered by <strong className="font-bold text-slate-800 dark:text-slate-200">Gridbase Bills</strong> — Motor Transaccional y Tributario Incrustado ({version})
                    </span>
                </div>
                <span className="text-[11px] text-slate-400 dark:text-slate-500">
                    Cumplimiento Fiscal DGII · Partida Doble Automatizada
                </span>
            </div>
        );
    }

    // Default: 'badge'
    return (
        <div
            className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-xs font-medium text-indigo-900 dark:text-indigo-200 shadow-2xs ${className}`}
        >
            <Zap className="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 fill-indigo-600 dark:fill-indigo-400 shrink-0" />
            <span>
                Powered by <strong className="font-bold text-indigo-950 dark:text-white">Gridbase Bills</strong>
            </span>
            <span className="text-[10px] font-bold uppercase px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25">
                {version}
            </span>
        </div>
    );
}
