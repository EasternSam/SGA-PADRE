/**
 * Utilidades de color para que las series de gráficos sigan siendo visibles
 * en ambos temas (p. ej. un color secundario casi negro sobre fondo oscuro).
 */

const HEX = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i;

/** Luminancia relativa WCAG (0 = negro, 1 = blanco) o `null` si no es un hex válido. */
export function luminance(hex) {
    if (typeof hex !== 'string' || !HEX.test(hex)) return null;

    let value = hex.slice(1);
    if (value.length === 3) value = [...value].map((c) => c + c).join('');

    const [r, g, b] = [0, 2, 4].map((i) => {
        const channel = parseInt(value.slice(i, i + 2), 16) / 255;
        return channel <= 0.03928 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

/**
 * Devuelve `color` si contrasta con el fondo del tema; si no, un color de reemplazo.
 * Umbrales: casi negro (< 0.03) en oscuro, casi blanco (> 0.85) en claro.
 */
export function visibleOn(color, dark, fallback = dark ? '#e7e5e4' : '#1c1917') {
    const l = luminance(color);
    if (l === null) return color;
    if (dark && l < 0.03) return fallback;
    if (!dark && l > 0.85) return fallback;
    return color;
}
