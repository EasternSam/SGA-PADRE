import React from 'react';
import { Link } from '@inertiajs/react';
import { ClipboardCheck } from 'lucide-react';
import { EmptyState, HeaderLink, WidgetHeader, formatNumber } from './shared';

export default function AdmissionsPipelineWidget({ data, palette }) {
    const stages = [
        { key: 'pending', label: 'Pendientes', color: palette.primary },
        { key: 'approved', label: 'Aprobadas', color: '#10b981' },
        { key: 'rejected', label: 'Rechazadas', color: palette.neutral },
    ];
    const total = stages.reduce((sum, s) => sum + data[s.key], 0);

    return (
        <>
            <WidgetHeader
                icon={ClipboardCheck}
                title="Admisiones"
                palette={palette}
                action={<HeaderLink href={data.href}>Ver solicitudes</HeaderLink>}
            />

            {total === 0 && data.prospects === 0 ? (
                <EmptyState
                    icon={ClipboardCheck}
                    title="No hay solicitudes de admisión"
                    description="Las solicitudes nuevas aparecerán aquí para su revisión."
                />
            ) : (
                <>
                    <ul className="grid grid-cols-3 gap-2">
                        {stages.map((s) => (
                            <li key={s.key} className="rounded-xl bg-gray-50/70 dark:bg-[#121212] border border-gray-100 dark:border-[#262626] px-3 py-2">
                                <span className="flex items-center gap-1.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                    <span className="size-2 rounded-full" style={{ backgroundColor: s.color }} />{s.label}
                                </span>
                                <span className="block text-lg font-black text-gray-900 dark:text-white tabular-nums mt-0.5">{formatNumber(data[s.key])}</span>
                            </li>
                        ))}
                    </ul>
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-3">
                        Prospectos en seguimiento: <strong className="text-gray-800 dark:text-gray-200">{formatNumber(data.prospects)}</strong>
                    </p>

                    {data.latest.length > 0 && (
                        <ul className="mt-4 space-y-2">
                            {data.latest.map((a) => (
                                <li key={a.id} className="flex items-center justify-between gap-2 text-xs">
                                    {data.href
                                        ? <Link href={data.href} className="font-semibold text-gray-800 dark:text-gray-200 truncate hover:underline">{a.name}</Link>
                                        : <span className="font-semibold text-gray-800 dark:text-gray-200 truncate">{a.name}</span>}
                                    <span className="text-[11px] text-gray-400 shrink-0">{a.time}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </>
            )}
        </>
    );
}
