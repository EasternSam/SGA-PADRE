import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { router, usePage } from '@inertiajs/react';
import { addWidget, isSameLayout, keysMissingData, moveWidget, removeWidget, resizeWidget } from './layoutModel';

const ENDPOINT = '/admin/dashboard/layout';

/**
 * Estado del dashboard modular: layout guardado, borrador en modo edición y
 * sincronización con el servidor (que siempre devuelve el layout ya saneado).
 */
export function useDashboardLayout({ initialLayout, catalog }) {
    const pageProps = usePage().props;
    const catalogByKey = useMemo(() => Object.fromEntries(catalog.map((w) => [w.key, w])), [catalog]);

    const [saved, setSaved] = useState(initialLayout);
    const [draft, setDraft] = useState(initialLayout);
    const [editing, setEditing] = useState(false);
    const [status, setStatus] = useState({ state: 'idle', message: null });

    const layout = editing ? draft : saved;
    const dirty = editing && !isSameLayout(draft, saved);

    // Widgets añadidos en modo edición llegan sin datos (son props opcionales): se piden una vez.
    const requested = useRef(new Set());
    useEffect(() => {
        const missing = keysMissingData(layout, pageProps).filter((key) => !requested.current.has(key));
        if (missing.length === 0) return;

        missing.forEach((key) => requested.current.add(key));
        router.reload({ only: missing.map((key) => `widget_${key}`) });
    }, [layout, pageProps]);

    // El aviso de "guardado" desaparece solo; los errores permanecen hasta la siguiente acción.
    useEffect(() => {
        if (status.state !== 'saved') return undefined;
        const timer = setTimeout(() => setStatus({ state: 'idle', message: null }), 4000);
        return () => clearTimeout(timer);
    }, [status]);

    const run = useCallback(async (request, onSuccess, successMessage) => {
        setStatus({ state: 'saving', message: null });
        try {
            const { data } = await request();
            onSuccess(data);
            setStatus({ state: 'saved', message: typeof successMessage === 'function' ? successMessage(data) : successMessage });
        } catch (error) {
            const message = error.response?.status === 422
                ? 'El diseño enviado no es válido.'
                : 'No se pudo guardar. Revisa tu conexión e inténtalo de nuevo.';
            setStatus({ state: 'error', message });
        }
    }, []);

    const applySaved = useCallback((next) => {
        setSaved(next);
        setDraft(next);
        setEditing(false);
    }, []);

    return {
        layout,
        catalogByKey,
        editing,
        dirty,
        status,
        start: () => {
            setDraft(saved);
            setEditing(true);
            setStatus({ state: 'idle', message: null });
        },
        cancel: () => {
            setDraft(saved);
            setEditing(false);
            setStatus({ state: 'idle', message: null });
        },
        move: (activeKey, overKey) => setDraft((prev) => moveWidget(prev, activeKey, overKey)),
        resize: (key, size) => setDraft((prev) => resizeWidget(prev, key, size, catalogByKey)),
        add: (key) => setDraft((prev) => addWidget(prev, catalogByKey[key])),
        remove: (key) => setDraft((prev) => removeWidget(prev, key)),
        save: () => run(() => axios.put(ENDPOINT, { widgets: draft }), (data) => applySaved(data.layout), 'Diseño guardado'),
        reset: () => run(() => axios.delete(ENDPOINT), (data) => applySaved(data.layout), 'Se restableció el diseño predeterminado'),
        saveAsRoleDefault: (role) => run(
            () => axios.put(`${ENDPOINT}/defaults/${encodeURIComponent(role)}`, { widgets: draft }),
            () => {},
            (data) => {
                const omitted = draft.length - data.layout.length;
                return `Predeterminado de ${data.role} actualizado`
                    + (omitted > 0 ? ` (se omitieron ${omitted} widgets que ese rol no puede ver)` : '');
            },
        ),
    };
}
