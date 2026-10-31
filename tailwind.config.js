import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,jsx,ts,tsx}',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Outfit', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // shadcn/ui semantic tokens
                border: "hsl(var(--border))",
                input: "hsl(var(--input))",
                ring: "hsl(var(--ring))",
                background: "hsl(var(--background))",
                foreground: "hsl(var(--foreground))",
                primary: {
                    DEFAULT: "hsl(var(--primary))",
                    foreground: "hsl(var(--primary-foreground))",
                },
                secondary: {
                    DEFAULT: "hsl(var(--secondary))",
                    foreground: "hsl(var(--secondary-foreground))",
                },
                destructive: {
                    DEFAULT: "hsl(var(--destructive))",
                    foreground: "hsl(var(--destructive-foreground))",
                },
                muted: {
                    DEFAULT: "hsl(var(--muted))",
                    foreground: "hsl(var(--muted-foreground))",
                },
                accent: {
                    DEFAULT: "hsl(var(--accent))",
                    foreground: "hsl(var(--accent-foreground))",
                },
                popover: {
                    DEFAULT: "hsl(var(--popover))",
                    foreground: "hsl(var(--popover-foreground))",
                },
                card: {
                    DEFAULT: "hsl(var(--card))",
                    foreground: "hsl(var(--card-foreground))",
                },

                // Academic+ Semantic Brand Tokens
                brand: {
                    DEFAULT: 'rgb(var(--color-primary, 33 17 112) / <alpha-value>)',
                    primary: 'rgb(var(--color-primary, 33 17 112) / <alpha-value>)',
                    secondary: 'rgb(var(--color-secondary, 26 10 83) / <alpha-value>)',
                    accent: 'rgb(var(--color-accent, 247 147 30) / <alpha-value>)',
                    neutral: 'rgb(var(--color-neutral, 100 116 139) / <alpha-value>)',
                    dark: 'color-mix(in srgb, var(--brand-primary, #211170) 85%, black)',
                },
                'brand-primary': 'rgb(var(--color-primary, 33 17 112) / <alpha-value>)',
                'brand-secondary': 'rgb(var(--color-secondary, 26 10 83) / <alpha-value>)',
                'brand-accent': 'rgb(var(--color-accent, 247 147 30) / <alpha-value>)',
                'brand-neutral': 'rgb(var(--color-neutral, 100 116 139) / <alpha-value>)',
                'brand-dark': 'color-mix(in srgb, var(--brand-primary, #211170) 85%, black)',

                // Bills Charcoal Semantic Surface Tokens
                surface: {
                    app: '#0A0A0A',
                    card: '#171717',
                    elevated: '#1f1f1f',
                    border: '#262626',
                    'border-hover': '#404040',
                    hover: '#262626',
                    muted: '#121212',
                },

                // Existing SGA dynamic and static colors (preserved 100%)
                'sga-primary': 'rgb(var(--color-primary) / <alpha-value>)',
                'sga-secondary': '#3b82f6',
                'sga-accent': '#10b981',
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
            borderRadius: {
                lg: "var(--radius)",
                md: "calc(var(--radius) - 2px)",
                sm: "calc(var(--radius) - 4px)",
            },
        },
    },

    plugins: [
        forms,
    ],
};
