import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // darkMode: 'class', 

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ===> AQUÍ ESTÁ EL CAMBIO CLAVE <===
                // 'sga-primary' ahora es DINÁMICO.
                // Lee la variable CSS --color-primary que inyectamos en el layout (app.blade.php)
                // <alpha-value> permite usar opacidades como bg-sga-primary/50
                'sga-primary': 'rgb(var(--color-primary) / <alpha-value>)', 
                
                // El resto de la paleta se mantiene fija para asegurar armonía visual
                'sga-secondary': '#3b82f6', // Azul brillante para highlights
                'sga-accent': '#10b981',    // Verde
                'sga-accent-purple': '#8b5cf6', 
                'sga-accent-red': '#ef4444', 
                
                'sga-text': '#1f2937', 
                'sga-text-light': '#6b7280', 
                'sga-gray': '#e5e7eb', 
                'sga-success': '#22c55e', 
                'sga-danger': '#ef4444', 
                'sga-warning': '#f59e0b', 
                'sga-info': '#3b82f6', 
                
                'sga-bg': '#f3f4f6', 
                'sga-card': '#ffffff', 
            },
        },
    },

    plugins: [
        forms,
    ],
};