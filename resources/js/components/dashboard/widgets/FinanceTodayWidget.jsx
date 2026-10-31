import React from 'react';
import { Banknote } from 'lucide-react';
import { HeaderLink, WidgetHeader, formatMoney, formatNumber } from './shared';

export default function FinanceTodayWidget({ data, palette }) {
    const trendMax = Math.max(1, ...data.trend.map((d) => d.total));
    const methodMax = Math.max(1, ...data.byMethod.map((m) => m.total));

    return (
        <>
            <WidgetHeader
                icon={Banknote}
                title="Cobros de hoy"
                palette={palette}
                action={<HeaderLink href={data.href}>Ir a caja</HeaderLink>}
            />

            <div>
                <span className="text-3xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">{formatMoney(data.total)}</span>
                <span className="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {data.count > 0
                        ? `${formatNumber(data.count)} ${data.count === 1 ? 'cobro registrado' : 'cobros registrados'}`
                        : data.lastPaymentAt ? `Sin cobros hoy · último: ${data.lastPaymentAt}` : 'Aún no hay cobros registrados'}
                </span>
            </div>

            {data.byMethod.length > 0 && (
                <ul className="mt-4 space-y-2">
                    {data.byMethod.map((m) => (
                        <li key={m.method} className="text-xs">
                            <div className="flex justify-between gap-2 mb-1">
                                <span className="font-semibold text-gray-700 dark:text-gray-300">{m.method}</span>
                                <span className="tabular-nums text-gray-500 dark:text-gray-400">{formatMoney(m.total)} · {m.count}</span>
                            </div>
                            <div className="h-1.5 rounded-full bg-gray-100 dark:bg-[#262626] overflow-hidden">
                                <div className="h-full rounded-full" style={{ width: `${(m.total / methodMax) * 100}%`, backgroundColor: palette.primary }} />
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <div className="mt-auto pt-4">
                <span className="text-[11px] font-semibold text-gray-400">Últimos 7 días</span>
                <div className="flex items-end gap-1.5 h-16 mt-2" role="img" aria-label="Cobros de los últimos 7 días">
                    {data.trend.map((d, i) => (
                        <div key={d.date} className="flex-1 flex flex-col items-center justify-end h-full gap-1">
                            <div
                                className="w-full rounded-t-sm min-h-[2px]"
                                style={{
                                    height: `${(d.total / trendMax) * 100}%`,
                                    backgroundColor: i === data.trend.length - 1 ? palette.primary : `${palette.primary}55`,
                                }}
                                title={`${d.label}: ${formatMoney(d.total)}`}
                            />
                            <span className="text-[9px] text-gray-400 font-semibold">{d.label.split(' ')[0]}</span>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}
