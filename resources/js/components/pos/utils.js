/**
 * Funciones de formato y utilidades para la Terminal de Caja (POS) y peticiones autenticadas.
 */

export function formatMoney(val) {
    const num = Number(val) || 0;
    return new Intl.NumberFormat('es-DO', {
        style: 'currency',
        currency: 'DOP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
}

export function getXsrfCookieToken() {
    if (typeof document === 'undefined' || !document.cookie) return '';
    const match = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='));
    return match ? decodeURIComponent(match.substring('XSRF-TOKEN='.length)) : '';
}

export function getCsrfToken(inertiaCsrfToken = null) {
    if (inertiaCsrfToken) return inertiaCsrfToken;
    if (typeof document === 'undefined') return '';
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export function getCsrfHeaders(inertiaCsrfToken = null) {
    const xsrfCookie = getXsrfCookieToken();
    if (xsrfCookie) {
        return { 'X-XSRF-TOKEN': xsrfCookie };
    }
    const token = getCsrfToken(inertiaCsrfToken);
    return token ? { 'X-CSRF-TOKEN': token } : {};
}
