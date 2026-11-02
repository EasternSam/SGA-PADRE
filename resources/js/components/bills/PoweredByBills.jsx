import React from 'react';

/**
 * Icono oficial de Gridbase Bills (isotipo geométrico "B" extraído de bills-logo.svg).
 */
export function BillsLogoIcon({ className = 'w-4 h-4' }) {
    return (
        <svg
            viewBox="85 0 265 290"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
            aria-hidden="true"
        >
            <path
                fill="#00a460"
                d="M109.28,284.96c-9.54,0-17.3-7.76-17.31-17.29-.03-33.78,0-95.42.11-188.41,0-8.71,4.05-16.89,10.93-22.2l.21-.42,1.59-.87,1.28-.87h.32L202.54,2.15c2.56-1.41,5.44-2.15,8.31-2.15,9.55,0,17.32,7.77,17.32,17.32v194.72c0,6.32-3.44,12.13-8.98,15.17l-86.29,47.34-15.4,8.31c-2.54,1.37-5.38,2.1-8.22,2.1h0Z"
            />
            <path
                fill="currentColor"
                d="M341.17,119.82c.2-2.13.32-4.25.32-6.43s-.12-4.3-.32-6.43c-3.08-30.17-27.53-54.01-57.97-56.16h-40.43v168.66c0,9.67-5.27,18.57-13.74,23.22l-87.04,47.75h136.47c32.44,0,59.14-24.75,62.37-56.46.2-2.13.32-4.25.32-6.45s-.12-4.3-.32-6.42c-2.3-22.5-16.39-41.51-35.98-50.62.05-.02.1-.05.15-.07,19.69-9.09,33.88-28.09,36.18-50.59Z"
            />
        </svg>
    );
}

/**
 * PoweredByBills — Sello minimalista oficial: únicamente el isotipo de Gridbase Bills + texto "Powered by Gridbase Bills".
 */
export default function PoweredByBills({
    variant = 'badge',
    className = '',
}) {
    if (variant === 'sidebar') {
        return (
            <div
                className={`mx-4 mb-2 flex items-center gap-1.5 text-[11px] text-white/65 select-none ${className}`}
                title="Powered by Gridbase Bills"
            >
                <BillsLogoIcon className="w-3.5 h-3.5 text-white shrink-0" />
                <span className="truncate">
                    Powered by <strong className="font-semibold text-white/90">Gridbase Bills</strong>
                </span>
            </div>
        );
    }

    if (variant === 'receipt') {
        return (
            <div
                className={`inline-flex items-center justify-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 select-none ${className}`}
            >
                <BillsLogoIcon className="w-3.5 h-3.5 text-slate-800 dark:text-white shrink-0" />
                <span>
                    Powered by <strong className="font-semibold text-slate-700 dark:text-slate-200">Gridbase Bills</strong>
                </span>
            </div>
        );
    }

    if (variant === 'banner') {
        return null;
    }

    if (variant === 'footer') {
        return (
            <div
                className={`mt-6 pt-4 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-end gap-1.5 text-xs text-slate-500 dark:text-slate-400 select-none ${className}`}
            >
                <BillsLogoIcon className="w-3.5 h-3.5 text-slate-800 dark:text-white shrink-0" />
                <span>
                    Powered by <strong className="font-semibold text-slate-700 dark:text-slate-200">Gridbase Bills</strong>
                </span>
            </div>
        );
    }

    // Default: 'badge' — Sello limpio solo con icono y texto
    return (
        <div
            className={`inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 select-none ${className}`}
            title="Powered by Gridbase Bills"
        >
            <BillsLogoIcon className="w-4 h-4 text-slate-800 dark:text-white shrink-0" />
            <span>
                Powered by <strong className="font-semibold text-slate-800 dark:text-slate-100">Gridbase Bills</strong>
            </span>
        </div>
    );
}
