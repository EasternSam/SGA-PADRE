import React, { useState } from 'react';
import {
    DndContext,
    DragOverlay,
    KeyboardSensor,
    PointerSensor,
    TouchSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import { SortableContext, rectSortingStrategy, sortableKeyboardCoordinates, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical } from 'lucide-react';
import { cn } from '@/lib/utils';
import { SIZE_CLASSES } from './layoutModel';
import WidgetFrame from './WidgetFrame';
import { WIDGETS } from './widgets';

function SortableWidget({ item, editing, children }) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: item.key, disabled: !editing });

    return (
        <div
            ref={setNodeRef}
            // Translate (no Transform): evita que las tarjetas se deformen al cruzar tamaños distintos.
            style={{ transform: CSS.Translate.toString(transform), transition }}
            className={cn('col-span-1 min-w-0', SIZE_CLASSES[item.size], isDragging && 'opacity-40')}
        >
            {children({ ...attributes, ...listeners })}
        </div>
    );
}

export default function DashboardGrid({ layout, catalogByKey, props, palette, editing, onMove, onResize, onRemove }) {
    const [activeKey, setActiveKey] = useState(null);
    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 6 } }),
        useSensor(TouchSensor, { activationConstraint: { delay: 200, tolerance: 8 } }),
        useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates }),
    );

    // Solo widgets con definición (permitidos) y componente conocido.
    const items = layout.filter((item) => catalogByKey[item.key] && WIDGETS[item.key]);
    const active = activeKey ? catalogByKey[activeKey] : null;

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={({ active: a }) => setActiveKey(a.id)}
            onDragEnd={({ active: a, over }) => {
                setActiveKey(null);
                onMove(a.id, over?.id ?? null);
            }}
            onDragCancel={() => setActiveKey(null)}
        >
            <SortableContext items={items.map((item) => item.key)} strategy={rectSortingStrategy}>
                <div className="grid grid-cols-1 md:grid-cols-6 xl:grid-cols-12 gap-4 sm:gap-6 items-stretch">
                    {items.map((item) => (
                        <SortableWidget key={item.key} item={item} editing={editing}>
                            {(dragHandle) => (
                                <WidgetFrame
                                    widget={WIDGETS[item.key]}
                                    definition={catalogByKey[item.key]}
                                    size={item.size}
                                    data={props[`widget_${item.key}`]}
                                    palette={palette}
                                    editing={editing}
                                    dragHandle={dragHandle}
                                    onResize={(size) => onResize(item.key, size)}
                                    onRemove={() => onRemove(item.key)}
                                />
                            )}
                        </SortableWidget>
                    ))}
                </div>
            </SortableContext>

            <DragOverlay dropAnimation={null}>
                {active && (
                    <div className="rounded-2xl bg-white dark:bg-[#171717] border border-gray-200 dark:border-[#333] shadow-2xl px-4 py-3 flex items-center gap-2 w-64">
                        <GripVertical className="size-4 text-gray-400" />
                        <span className="text-sm font-bold text-gray-900 dark:text-white truncate">{active.title}</span>
                    </div>
                )}
            </DragOverlay>
        </DndContext>
    );
}
