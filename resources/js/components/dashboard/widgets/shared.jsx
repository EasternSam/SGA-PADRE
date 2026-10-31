import React, { useEffect, useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    Activity,
    BookOpen,
    Building2,
    CalendarDays,
    CalendarPlus,
    ClipboardCheck,
    CreditCard,
    FileText,
    GraduationCap,
    Inbox,
    Receipt,
    TriangleAlert,
    UserPlus,
    Users,
    Wallet,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { visibleOn } from '../color';

/** Íconos que el servidor referencia por nombre (QuickActions, OperationsHub). */
export const ICONS = {
    'user-plus': UserPlus,
    'clipboard-check': ClipboardCheck,
    'credit-card': CreditCard,
    'calendar-plus': CalendarPlus,
    'calendar-days': CalendarDays,
    wallet: Wallet,
    receipt: Receipt,
    'book-open': BookOpen,
    building: Building2,
    'file-text': FileText,
    'graduation-cap': GraduationCap,
    users: Users,
    activity: Activity,
};

const moneyFormatter = new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP', maximumFractionDigits: 0 });
const numberFormatter = new Intl.NumberFormat('es-DO');

export const formatMoney = (value) => moneyFormatter.format(Number(value) || 0);
export const formatNumber = (value) => numberFormatter.format(Number(value) || 0);

/** `true` mientras <html> tenga la clase `dark` (se actualiza al cambiar de tema). */
function useIsDark() {
    const [dark, setDark] = useState(() => typeof document !== 'undefined' && document.documentElement.classList.contains('dark'));

    useEffect(() => {
        const root = document.documentElement;
        const observer = new MutationObserver(() => setDark(root.classList.contains('dark')));
        observer.observe(root, { attributes: true, attributeFilter: ['class'] });
        return () => observer.disconnect();
    }, []);

    return dark;
}

/**
 * Paleta institucional (Settings → branding) con los valores por defecto de la marca.
 * `ink` es el secundario ajustado al tema: un secundario casi negro se aclara en modo oscuro.
 */
export function usePalette() {
    const { branding } = usePage().props;
    const dark = useIsDark();

    return useMemo(() => {
        const secondary = branding?.palette?.secondary || '#1a0a53';

        return {
            primary: branding?.palette?.primary || '#211170',
            secondary,
            ink: visibleOn(secondary, dark),
            accent: branding?.palette?.accent || '#f7931e',
            neutral: branding?.palette?.neutral || '#64748b',
            muted: '#a8a29e',
        };
    }, [branding, dark]);
}

/** Colores ordenados para series categóricas (los "Otros" usan `palette.muted`). */
export const seriesColors = (palette) => [palette.primary, palette.accent, palette.neutral, palette.ink];

export function WidgetHeader({ icon: Icon, title, subtitle, action, palette, className }) {
    return (
        <div className={cn('flex items-start justify-between gap-3 pb-3 mb-4 border-b border-gray-100 dark:border-[#262626]', className)}>
            <div className="flex items-center gap-2 min-w-0">
                {Icon && (
                    <span
                        className="size-6 rounded-lg flex items-center justify-center shrink-0"
                        style={{ backgroundColor: `${palette.primary}18`, color: palette.primary }}
                    >
                        <Icon className="size-3.5" />
                    </span>
                )}
                <div className="min-w-0">
                    <h3 className="text-base font-bold text-gray-900 dark:text-white tracking-tight truncate">{title}</h3>
                    {subtitle && <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{subtitle}</p>}
                </div>
            </div>
            {action && <div className="shrink-0">{action}</div>}
        </div>
    );
}

export function HeaderLink({ href, children }) {
    if (!href) return null;

    return (
        <Link href={href} className="text-xs font-semibold text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            {children}
        </Link>
    );
}

export function EmptyState({ icon: Icon = Inbox, title, description, action }) {
    return (
        <div className="flex-1 flex flex-col items-center justify-center text-center py-8 px-4">
            <span className="size-10 rounded-xl bg-gray-100 dark:bg-[#1f1f1f] text-gray-400 flex items-center justify-center mb-3">
                <Icon className="size-5" />
            </span>
            <p className="text-sm font-semibold text-gray-700 dark:text-gray-200">{title}</p>
            {description && <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs">{description}</p>}
            {action && <div className="mt-3">{action}</div>}
        </div>
    );
}

export function WidgetError() {
    return (
        <EmptyState
            icon={TriangleAlert}
            title="No se pudo cargar este widget"
            description="El error quedó registrado. Recarga la página o inténtalo más tarde."
        />
    );
}

export function WidgetSkeleton() {
    return (
        <div className="flex-1 space-y-3 animate-pulse" aria-busy="true" aria-label="Cargando">
            <div className="h-4 w-1/3 rounded bg-gray-100 dark:bg-[#1f1f1f]" />
            <div className="h-24 rounded-xl bg-gray-100 dark:bg-[#1f1f1f]" />
            <div className="h-4 w-2/3 rounded bg-gray-100 dark:bg-[#1f1f1f]" />
        </div>
    );
}

/** Selector compacto usado en los encabezados de los gráficos. */
export function ViewSelect({ value, onChange, options, label }) {
    return (
        <select
            value={value}
            onChange={(e) => onChange(e.target.value)}
            aria-label={label}
            className="text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-[#121212] border border-gray-200/80 dark:border-[#262626] rounded-xl pl-3 pr-8 py-1.5 cursor-pointer focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-[#404040]"
        >
            {options.map((opt) => (
                <option key={opt.value} value={opt.value}>{opt.label}</option>
            ))}
        </select>
    );
}
