import React from 'react';
import { Wallet } from 'lucide-react';
import { EmptyState, HeaderLink, WidgetHeader, formatMoney, formatNumber } from './shared';

/** De "por vencer" a "+90 días": la intensidad sube con la antigüedad. */
const AGING_SHADES = { current: '33', '1_30': '66', '31_60': '99', '61_90': 'cc', '90_plus': '' };

export default function ReceivablesWidget({ data, palette }) {
    if (data.count === 0) {
        return (
            <>
                <WidgetHeader icon={Wallet} title="Cuentas por cobrar" palette={palette} />
                <EmptyState icon={Wallet} title="No hay saldos pendientes" description="Todos los pagos registrados están saldados." />
            </>
        );
    }

    const color = (key) => `${palette.primary}${AGING_SHADES[key]}`;

    return (
        <>
            <WidgetHeader
                icon={Wallet}
                title="Cuentas por cobrar"
                palette={palette}
                action={<HeaderLink href={data.href}>Libro mayor</HeaderLink>}
            />

            <div className="grid grid-cols-2 gap-3">
                <div>
                    <span className="text-[11px] font-semibold text-gray-500 dark:text-gray-400">Pendiente total</span>
                    <span className="block text-2xl font-black text-gray-900 dark:text-white tabular-nums">{formatMoney(data.total)}</span>
                    <span className="text-[11px] text-gray-400">{formatNumber(data.count)} pagos</span>
                </div>
                <div>
                    <span className="text-[11px] font-semibold text-gray-500 dark:text-gray-400">Vencido</span>
                    <span className="block text-2xl font-black tabular-nums" style={{ color: palette.primary }}>{formatMoney(data.overdueTotal)}</span>
                    <span className="text-[11px] text-gray-400">{formatNumber(data.overdueCount)} pagos</span>
                </div>
            </div>

            <div className="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-[#262626] mt-4" role="img" aria-label="Antigüedad de la deuda">
                {data.aging.map((b) => (
                    <div key={b.key} style={{ width: `${(b.total / data.total) * 100}%`, backgroundColor: color(b.key) }} title={`${b.label}: ${formatMoney(b.total)}`} />
                ))}
            </div>
            <ul className="flex flex-wrap gap-x-3 gap-y-1 mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                {data.aging.map((b) => (
                    <li key={b.key} className="flex items-center gap-1.5">
                        <span className="size-2 rounded-full" style={{ backgroundColor: color(b.key) }} />
                        {b.label}: <strong className="text-gray-700 dark:text-gray-300 tabular-nums">{formatMoney(b.total)}</strong>
                    </li>
                ))}
            </ul>

            {data.topDebtors.length > 0 && (
                <div className="mt-4 pt-3 border-t border-gray-100 dark:border-[#262626]">
                    <span className="text-[11px] font-semibold text-gray-400">Mayor saldo vencido</span>
                    <ul className="mt-2 space-y-1.5">
                        {data.topDebtors.map((d) => (
                            <li key={d.name} className="flex items-center justify-between gap-2 text-xs">
                                <span className="font-semibold text-gray-800 dark:text-gray-200 truncate">{d.name}</span>
                                <span className="shrink-0 tabular-nums text-gray-500 dark:text-gray-400">
                                    <strong className="text-gray-900 dark:text-white">{formatMoney(d.total)}</strong> · {d.maxDays} días
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </>
    );
}
