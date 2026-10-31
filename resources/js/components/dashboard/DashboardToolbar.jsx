import React, { useEffect, useState } from 'react';
import * as DropdownMenu from '@radix-ui/react-dropdown-menu';
import { Check, ChevronDown, LoaderCircle, Plus, RotateCcw, SlidersHorizontal, TriangleAlert, Users } from 'lucide-react';
import { cn } from '@/lib/utils';

const BUTTON = 'h-9 px-3 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors disabled:opacity-50 disabled:pointer-events-none';
const GHOST = `${BUTTON} text-gray-600 dark:text-gray-300 bg-white dark:bg-[#171717] border border-gray-200 dark:border-[#262626] hover:bg-gray-50 dark:hover:bg-white/5`;

function StatusMessage({ status }) {
    if (status.state === 'saving') {
        return <span className="text-xs text-gray-500 flex items-center gap-1.5"><LoaderCircle className="size-3.5 animate-spin" />Guardando…</span>;
    }
    if (status.state === 'saved') {
        return <span role="status" className="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5"><Check className="size-3.5" />{status.message}</span>;
    }
    if (status.state === 'error') {
        return <span role="alert" className="text-xs font-semibold text-rose-600 dark:text-rose-400 flex items-center gap-1.5"><TriangleAlert className="size-3.5" />{status.message}</span>;
    }
    return null;
}

/** Restablecer pide una segunda pulsación en lugar de un diálogo nativo. */
function ResetButton({ onReset, disabled }) {
    const [armed, setArmed] = useState(false);

    useEffect(() => {
        if (!armed) return undefined;
        const timer = setTimeout(() => setArmed(false), 4000);
        return () => clearTimeout(timer);
    }, [armed]);

    return (
        <button
            type="button"
            disabled={disabled}
            onClick={() => {
                if (!armed) return setArmed(true);
                setArmed(false);
                onReset();
            }}
            className={cn(GHOST, armed && 'text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/30')}
        >
            <RotateCcw className="size-3.5" />{armed ? '¿Confirmar?' : 'Restablecer'}
        </button>
    );
}

export default function DashboardToolbar({ state, palette, canManageRoleDefaults, dashboardRoles, onOpenLibrary }) {
    const busy = state.status.state === 'saving';

    if (!state.editing) {
        return (
            <div className="flex items-center justify-end gap-3">
                <StatusMessage status={state.status} />
                <button type="button" onClick={state.start} className={GHOST}>
                    <SlidersHorizontal className="size-3.5" />Personalizar
                </button>
            </div>
        );
    }

    return (
        <div className="flex flex-wrap items-center justify-end gap-2">
            <StatusMessage status={state.status} />
            <button type="button" onClick={onOpenLibrary} disabled={busy} className={GHOST}>
                <Plus className="size-3.5" />Agregar widget
            </button>
            <ResetButton onReset={state.reset} disabled={busy} />

            {canManageRoleDefaults && (
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger asChild>
                        <button type="button" disabled={busy} className={GHOST} title="Usar este diseño como predeterminado de un rol">
                            <Users className="size-3.5" />Predeterminado<ChevronDown className="size-3.5" />
                        </button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Portal>
                        <DropdownMenu.Content align="end" sideOffset={6} className="z-50 min-w-[200px] rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-1 shadow-xl">
                            <DropdownMenu.Label className="px-2.5 py-1.5 text-[11px] font-semibold text-gray-400">Diseño inicial para el rol…</DropdownMenu.Label>
                            {dashboardRoles.map((role) => (
                                <DropdownMenu.Item
                                    key={role}
                                    onSelect={() => state.saveAsRoleDefault(role)}
                                    className="px-2.5 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 outline-none cursor-pointer data-[highlighted]:bg-gray-100 dark:data-[highlighted]:bg-white/5"
                                >
                                    {role}
                                </DropdownMenu.Item>
                            ))}
                        </DropdownMenu.Content>
                    </DropdownMenu.Portal>
                </DropdownMenu.Root>
            )}

            <button type="button" onClick={state.cancel} disabled={busy} className={GHOST}>Cancelar</button>
            <button
                type="button"
                onClick={state.save}
                disabled={busy || !state.dirty}
                className={`${BUTTON} text-white shadow-sm hover:opacity-90`}
                style={{ backgroundColor: palette.primary }}
            >
                <Check className="size-3.5" />Guardar
            </button>
        </div>
    );
}
