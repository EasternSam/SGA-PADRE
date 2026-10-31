import React, { useMemo, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { LayoutGrid, Plus } from 'lucide-react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { SquigglyText } from '@/components/ui/squiggly-text';
import DashboardGrid from '@/components/dashboard/DashboardGrid';
import DashboardToolbar from '@/components/dashboard/DashboardToolbar';
import WidgetLibrary from '@/components/dashboard/WidgetLibrary';
import { useDashboardLayout } from '@/components/dashboard/useDashboardLayout';
import { EmptyState, usePalette } from '@/components/dashboard/widgets/shared';

function greetingFor(hour) {
    if (hour >= 5 && hour < 12) return 'Buenos días';
    if (hour >= 12 && hour < 19) return 'Buenas tardes';
    return 'Buenas noches';
}

/**
 * Dashboard modular: cada usuario elige, ordena y dimensiona sus widgets.
 * Catálogo, permisos y datos llegan del servidor (AdminDashboardController).
 */
export default function Dashboard({ layout: initialLayout, catalog, canManageRoleDefaults, dashboardRoles, currentDate }) {
    const props = usePage().props;
    const palette = usePalette();
    const state = useDashboardLayout({ initialLayout, catalog });
    const [libraryOpen, setLibraryOpen] = useState(false);
    const greeting = useMemo(() => greetingFor(new Date().getHours()), []);

    const openLibrary = () => {
        if (!state.editing) state.start();
        setLibraryOpen(true);
    };

    return (
        <AuthenticatedLayout title="Panel de control">
            <Head title="Panel de control" />

            <div className="space-y-6">
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div className="lg:shrink-0">
                        <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center flex-wrap gap-x-2">
                            <span>{greeting},</span>
                            <SquigglyText scale={[0.8, 1.2]} stepDuration={140} baseFrequency={0.015} style={{ color: palette.primary }} className="font-black">
                                {props.auth?.user?.name}
                            </SquigglyText>
                        </h1>
                        <p className="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mt-1 first-letter:uppercase">{currentDate}</p>
                    </div>
                    <div className="lg:flex-1 min-w-0">
                        <DashboardToolbar
                            state={state}
                            palette={palette}
                            canManageRoleDefaults={canManageRoleDefaults}
                            dashboardRoles={dashboardRoles}
                            onOpenLibrary={openLibrary}
                        />
                    </div>
                </div>

                {state.editing && (
                    <p className="text-xs text-gray-500 dark:text-gray-400 rounded-xl border border-dashed border-gray-300 dark:border-[#333] px-4 py-2.5">
                        Arrastra desde <strong>⋮⋮</strong> para reordenar, elige el tamaño (S · M · L · XL) o quita widgets. Los cambios se aplican al pulsar <strong>Guardar</strong>.
                    </p>
                )}

                {state.layout.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-gray-300 dark:border-[#333] bg-white/60 dark:bg-[#171717]/60">
                        <EmptyState
                            icon={LayoutGrid}
                            title="Tu panel está vacío"
                            description="Agrega los widgets que quieras ver. Solo aparecen los que tu rol puede usar."
                            action={(
                                <button
                                    type="button"
                                    onClick={openLibrary}
                                    className="h-9 px-3 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 hover:opacity-90"
                                    style={{ backgroundColor: palette.primary }}
                                >
                                    <Plus className="size-3.5" />Agregar widgets
                                </button>
                            )}
                        />
                    </div>
                ) : (
                    <DashboardGrid
                        layout={state.layout}
                        catalogByKey={state.catalogByKey}
                        props={props}
                        palette={palette}
                        editing={state.editing}
                        onMove={state.move}
                        onResize={state.resize}
                        onRemove={state.remove}
                    />
                )}
            </div>

            <WidgetLibrary
                open={libraryOpen}
                onOpenChange={setLibraryOpen}
                catalog={catalog}
                layout={state.layout}
                onAdd={state.add}
                palette={palette}
            />
        </AuthenticatedLayout>
    );
}