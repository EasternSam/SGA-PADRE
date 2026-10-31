import { useState, useEffect, useCallback } from 'react';

/**
 * Hook reutilizable para control de tema (Modo Claro / Modo Oscuro)
 * con persistencia en localStorage y soporte para View Transitions circulares.
 */
export function useTheme() {
    const [darkMode, setDarkMode] = useState(false);

    useEffect(() => {
        const stored = localStorage.getItem('darkMode');
        const isDark = stored === 'true' || 
            (stored === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
        setDarkMode(isDark);
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }, []);

    const toggleTheme = useCallback((targetTheme, trigger) => {
        const next = typeof targetTheme === 'string' ? targetTheme === 'dark' : !darkMode;

        const updateStateAndDOM = () => {
            setDarkMode(next);
            localStorage.setItem('darkMode', String(next));
            if (next) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        };

        const isAppearanceTransition = typeof document !== 'undefined'
            && document.startViewTransition
            && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!isAppearanceTransition || !trigger) {
            updateStateAndDOM();
            return;
        }

        const rect = trigger.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        const endRadius = Math.hypot(
            Math.max(x, window.innerWidth - x),
            Math.max(y, window.innerHeight - y)
        );

        document.documentElement.classList.add('theme-transitioning');

        const transition = document.startViewTransition(() => {
            updateStateAndDOM();
        });

        transition.ready.then(() => {
            const anim = document.documentElement.animate(
                {
                    clipPath: [
                        `circle(0px at ${x}px ${y}px)`,
                        `circle(${endRadius}px at ${x}px ${y}px)`
                    ]
                },
                {
                    duration: 350,
                    easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
                    pseudoElement: '::view-transition-new(root)'
                }
            );
            anim.onfinish = () => {
                document.documentElement.classList.remove('theme-transitioning');
            };
        }).catch(() => {
            document.documentElement.classList.remove('theme-transitioning');
        });
    }, [darkMode]);

    return {
        darkMode,
        theme: darkMode ? 'dark' : 'light',
        setDarkMode,
        toggleTheme,
    };
}

export default useTheme;
