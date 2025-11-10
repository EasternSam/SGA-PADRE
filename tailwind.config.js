import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // --- CORRECCIÓN ---
    // Mantenemos el modo oscuro deshabilitado
    // darkMode: 'class', 

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // --- ¡ACTUALIZADO! ---
            // Paleta de colores redefinida para coincidir con la imagen de inspiración.
            colors: {
                // 'sga-primary': '#0055A4', // Azul oscuro (Original)
                'sga-primary': '#1e3a8a', // Azul-indigo oscuro para el sidebar (Inspiración)
                // 'sga-secondary': '#E6F0FF', // Azul claro (Original)
                'sga-secondary': '#3b82f6', // Azul brillante para highlights (Inspiración)
                'sga-accent': '#10b981', // Verde (Inspiración - Donut Chart)
                'sga-accent-purple': '#8b5cf6', // Púrpura (Inspiración - Donut Chart)
                'sga-accent-red': '#ef4444', // Rojo (Inspiración - Donut Chart)
                
                'sga-text': '#1f2937', // Texto principal (gris-900)
                'sga-text-light': '#6b7280', // Texto secundario (gris-500)
                'sga-gray': '#e5e7eb', // Bordes (gris-200)
                'sga-success': '#22c55e', // Verde éxito
                'sga-danger': '#ef4444', // Rojo peligro
                'sga-warning': '#f59e0b', // Naranja advertencia
                'sga-info': '#3b82f6', // Azul información
                
                // 'sga-bg': '#F9FAFB', // Original (gris-50)
                'sga-bg': '#f3f4f6', // Fondo de la app (gris-100)
                'sga-card': '#ffffff', // Fondo de tarjetas
            },
        },
    },

    plugins: [
        forms,
    ],
};