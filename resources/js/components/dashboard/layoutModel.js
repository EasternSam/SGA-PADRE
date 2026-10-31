/**
 * Operaciones puras sobre el layout del dashboard: [{ key, size }] en orden visual.
 * Nunca mutan la entrada; si no hay cambio devuelven la misma referencia.
 */

/** Columnas por tamaño (clases literales para que Tailwind las detecte). */
export const SIZE_CLASSES = {
    sm: 'md:col-span-3 xl:col-span-4',
    md: 'md:col-span-6 xl:col-span-6',
    lg: 'md:col-span-6 xl:col-span-8',
    xl: 'md:col-span-6 xl:col-span-12',
};

export const SIZE_LABELS = { sm: 'S', md: 'M', lg: 'L', xl: 'XL' };

export function moveWidget(layout, activeKey, overKey) {
    const from = layout.findIndex((w) => w.key === activeKey);
    const to = layout.findIndex((w) => w.key === overKey);

    if (from === -1 || to === -1 || from === to) {
        return layout;
    }

    const next = layout.slice();
    const [moved] = next.splice(from, 1);
    next.splice(to, 0, moved);

    return next;
}

export function resizeWidget(layout, key, size, catalogByKey) {
    const allowed = catalogByKey[key]?.sizes ?? [];
    const current = layout.find((w) => w.key === key);

    if (!current || current.size === size || !allowed.includes(size)) {
        return layout;
    }

    return layout.map((w) => (w.key === key ? { ...w, size } : w));
}

export function addWidget(layout, definition) {
    if (!definition || layout.some((w) => w.key === definition.key)) {
        return layout;
    }

    return [...layout, { key: definition.key, size: definition.defaultSize }];
}

export function removeWidget(layout, key) {
    return layout.some((w) => w.key === key) ? layout.filter((w) => w.key !== key) : layout;
}

export function isSameLayout(a, b) {
    return a.length === b.length && a.every((w, i) => w.key === b[i].key && w.size === b[i].size);
}

/** Widgets del layout cuyo prop `widget_<key>` aún no llegó del servidor. */
export function keysMissingData(layout, props) {
    return layout.map((w) => w.key).filter((key) => props[`widget_${key}`] === undefined);
}
