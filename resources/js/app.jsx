import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import ModalStandardizer from './components/layout/ModalStandardizer';

const appName = 'SGA';

const el = document.getElementById('app');
let initialPage;
if (el && el.dataset && el.dataset.page) {
    try {
        initialPage = JSON.parse(el.dataset.page);
    } catch (e) {
        console.error('Failed to parse initial Inertia page:', e);
    }
}

createInertiaApp({
    page: initialPage,
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <>
                <App {...props} />
                <ModalStandardizer />
            </>
        );
    },
    progress: {
        color: initialPage?.props?.branding?.primary_color || '#bb3902',
        showSpinner: false,
        delay: 50,
    },
});
