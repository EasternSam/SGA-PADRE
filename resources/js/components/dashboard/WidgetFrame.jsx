import React from 'react';
import { GripVertical, X } from 'lucide-react';
import { cn } from '@/lib/utils';
import { SIZE_LABELS } from './layoutModel';
import { WidgetError, WidgetSkeleton } from './widgets/shared';

const CARD = 'p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#171717] border border-gray-100 dark:border-[#262626] shadow-sm';

/** Barra de edición: arrastrar, cambiar tamaño (solo los permitidos) y quitar. */
function EditBar({ definition, size, onResize, onRemove, dragHandle }) {
    return (
        <div className="flex items-center gap-2 mb-3 rounded-xl bg-gray-50 dark:bg-[#121212] border border-gray-200/80 dark:border-[#262626] px-2 py-1.5">
            <button
                type="button"
                {...dragHandle}
                aria-label={`Mover ${definition.title}`}
                className="size-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-white/5 cursor-grab active:cursor-grabbing touch-none"
            >
                <GripVertical className="size-4" />
            </button>
            <span className="text-xs font-bold text-gray-700 dark:text-gray-200 truncate flex-1">{definition.title}</span>
            {definition.sizes.length > 1 && (
                <div role="radiogroup" aria-label="Tamaño" className="flex items-center rounded-lg bg-gray-100 dark:bg-[#1f1f1f] p-0.5">
                    {definition.sizes.map((option) => (
                        <button
                            key={option}
                            type="button"
                            role="radio"
                            aria-checked={option === size}
                            onClick={() => onResize(option)}
                            className={cn(
                                'h-6 min-w-7 px-1.5 rounded-md text-[11px] font-bold transition-colors',
                                option === size
                                    ? 'bg-white dark:bg-[#2a2a2a] text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200',
                            )}
                        >
                            {SIZE_LABELS[option]}
                        </button>
                    ))}
                </div>
            )}
            <button
                type="button"
                onClick={onRemove}
                aria-label={`Quitar ${definition.title}`}
                className="size-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"
            >
                <X className="size-4" />
            </button>
        </div>
    );
}

export default function WidgetFrame({ widget, definition, size, data, palette, editing, onResize, onRemove, dragHandle }) {
    const Component = widget.component;
    const body = data === undefined
        ? <WidgetSkeleton />
        : data.error ? <WidgetError /> : <Component data={data} size={size} palette={palette} />;

    // En edición el contenido no es interactivo: evita navegar o abrir menús al arrastrar.
    const content = <div className={cn('flex-1 flex flex-col', editing && 'pointer-events-none select-none')}>{body}</div>;

    if (widget.bare && !editing && data !== undefined && !data.error) {
        return <div className="h-full">{content}</div>;
    }

    return (
        <div className={cn(CARD, 'h-full flex flex-col', editing && 'outline-dashed outline-2 outline-offset-4 outline-gray-200 dark:outline-[#333]')}>
            {editing && <EditBar definition={definition} size={size} onResize={onResize} onRemove={onRemove} dragHandle={dragHandle} />}
            {content}
        </div>
    );
}
