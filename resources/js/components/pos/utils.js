/**
 * Funciones de formato y utilidades para la Terminal de Caja (POS).
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

export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}
