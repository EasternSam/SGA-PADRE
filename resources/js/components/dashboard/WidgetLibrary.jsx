import React from 'react';
import { Check, Plus } from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { SIZE_LABELS } from './layoutModel';

const CATEGORIES = [
    { key: 'general', label: 'General' },
    { key: 'academico', label: 'Académico' },
    { key: 'finanzas', label: 'Finanzas' },
];

/** Biblioteca de widgets: solo lista lo que el servidor permitió para el usuario. */
export default function WidgetLibrary({ open, onOpenChange, catalog, layout, onAdd, palette }) {
    const inLayout = new Set(layout.map((w) => w.key));

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-2xl max-h-[85vh] overflow-y-auto dark:bg-[#171717] dark:border-[#262626]">
                <DialogHeader>
                    <DialogTitle>Agregar widgets</DialogTitle>
                    <DialogDescription>Elige qué quieres ver en tu panel. Puedes moverlos y cambiar su tamaño después.</DialogDescription>
                </DialogHeader>

                {CATEGORIES.map((category) => {
                    const widgets = catalog.filter((w) => w.category === category.key);
                    if (widgets.length === 0) return null;

                    return (
                        <section key={category.key}>
                            <h4 className="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">{category.label}</h4>
                            <ul className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                {widgets.map((widget) => {
                                    const added = inLayout.has(widget.key);
                                    return (
                                        <li key={widget.key} className="rounded-xl border border-gray-100 dark:border-[#262626] bg-gray-50/60 dark:bg-[#121212] p-3 flex flex-col gap-2">
                                            <div className="min-w-0">
                                                <p className="text-sm font-bold text-gray-900 dark:text-white">{widget.title}</p>
                                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{widget.description}</p>
                                            </div>
                                            <div className="flex items-center justify-between gap-2 mt-auto">
                                                <span className="text-[10px] font-semibold text-gray-400">
                                                    Tamaños: {widget.sizes.map((s) => SIZE_LABELS[s]).join(' · ')}
                                                </span>
                                                <button
                                                    type="button"
                                                    disabled={added}
                                                    onClick={() => onAdd(widget.key)}
                                                    className="h-7 px-2.5 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors disabled:cursor-default"
                                                    style={added
                                                        ? { color: '#10b981', backgroundColor: '#10b98115' }
                                                        : { color: palette.primary, backgroundColor: `${palette.primary}15` }}
                                                >
                                                    {added ? <><Check className="size-3.5" />Agregado</> : <><Plus className="size-3.5" />Agregar</>}
                                                </button>
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        </section>
                    );
                })}
            </DialogContent>
        </Dialog>
    );
}
