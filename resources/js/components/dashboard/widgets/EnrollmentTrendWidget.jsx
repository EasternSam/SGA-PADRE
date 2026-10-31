import React, { useMemo, useState } from 'react';
import { TrendingUp } from 'lucide-react';
import { LineChart } from '@/components/ui/LineChart';
import { EmptyState, ViewSelect, WidgetHeader, formatNumber } from './shared';

export default function EnrollmentTrendWidget({ data, palette }) {
    const [view, setView] = useState('monthly');
    const monthly = data.monthly ?? [];
    const periods = data.periods ?? [];
    const hasData = data.totalYear > 0 || periods.some((p) => p.count > 0);

    const options = [
        { value: 'monthly', label: `${data.year} · Mensual` },
        { value: 'cumulative', label: 'Acumulado' },
        ...(periods.length > 0 ? [{ value: 'periods', label: 'Por período' }] : []),
    ];

    const series = useMemo(() => {
        if (view === 'cumulative') return [{ key: 'acumulado', label: 'Inscripciones acumuladas', color: palette.primary, area: true }];
        if (view === 'periods') return [{ key: 'periodo', label: 'Inscripciones por período', color: palette.primary, area: true }];

        return [
            { key: 'actual', label: `${data.year}`, color: palette.primary, area: true },
            ...(data.hasPreviousYear ? [{ key: 'anterior', label: `${data.year - 1}`, color: '#9ca3af', dashed: true, area: false }] : []),
        ];
    }, [view, palette.primary, data.year, data.hasPreviousYear]);

    const points = useMemo(() => {
        if (view === 'periods') {
            return periods.map((p, i) => ({ key: `p-${i}`, label: p.fullLabel, axisLabel: p.label, values: { periodo: p.count } }));
        }

        return monthly.map((m, i) => ({
            key: `m-${i}`,
            label: m.fullLabel,
            axisLabel: m.label,
            values: view === 'cumulative'
                ? { acumulado: m.cumulative }
                : { actual: m.count, ...(data.hasPreviousYear ? { anterior: m.previousCount } : {}) },
        }));
    }, [view, monthly, periods, data.hasPreviousYear]);

    const badge = data.growthRate === null
        ? <span className="text-[11px] font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-[#1f1f1f] border border-gray-200 dark:border-[#262626] px-2 py-0.5 rounded-full">Sin año previo</span>
        : (
            <span
                style={{ color: palette.primary, backgroundColor: `${palette.primary}14`, borderColor: `${palette.primary}30` }}
                className="text-[11px] font-bold border px-2 py-0.5 rounded-full"
            >
                {data.growthRate > 0 ? '+' : ''}{data.growthRate}% vs {data.year - 1}
            </span>
        );

    return (
        <>
            <WidgetHeader
                icon={TrendingUp}
                title="Inscripciones"
                palette={palette}
                action={hasData ? <ViewSelect value={view} onChange={setView} options={options} label="Vista de inscripciones" /> : null}
            />

            {hasData ? (
                <>
                    <div className="flex items-center gap-2 mb-2">{badge}</div>
                    <LineChart
                        data={points}
                        series={series}
                        label="Inscripciones"
                        unit="inscripciones"
                        height={180}
                        legend
                        categoryLabel={view === 'periods' ? 'Período' : 'Mes'}
                        className="w-full"
                    />
                    <div className="mt-auto pt-3 border-t border-gray-100 dark:border-[#262626] flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            {data.peak
                                ? <>Mes pico: <strong className="text-gray-800 dark:text-gray-200">{data.peak.label} ({formatNumber(data.peak.count)})</strong></>
                                : 'Sin inscripciones este año'}
                        </span>
                        <span className="font-semibold text-gray-700 dark:text-gray-300">
                            Total {data.year}: <strong style={{ color: palette.primary }}>{formatNumber(data.totalYear)}</strong>
                        </span>
                    </div>
                </>
            ) : (
                <EmptyState icon={TrendingUp} title={`Sin inscripciones en ${data.year}`} description="Cuando se registren inscripciones verás aquí su evolución mensual." />
            )}
        </>
    );
}
