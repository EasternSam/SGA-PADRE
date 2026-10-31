import { useCallback, useSyncExternalStore } from "react";

const screens = {
    sm: "640px",
    md: "768px",
    lg: "1024px",
    xl: "1280px",
    "2xl": "1536px",
};

/**
 * Checks whether a particular Tailwind CSS viewport size applies.
 *
 * @param size The size to check, which must either be included in Tailwind CSS's
 * list of default screen sizes, or added to the Tailwind CSS config file.
 *
 * @returns A boolean indicating whether the viewport size applies.
 */
export const useBreakpoint = (size) => {
    const query = `(min-width: ${screens[size] || "1024px"})`;

    const subscribe = useCallback(
        (onChange) => {
            if (typeof window === "undefined" || !window.matchMedia) {
                return () => {};
            }
            const breakpoint = window.matchMedia(query);
            breakpoint.addEventListener("change", onChange);
            return () => breakpoint.removeEventListener("change", onChange);
        },
        [query],
    );

    return useSyncExternalStore(
        subscribe,
        () => (typeof window !== "undefined" && window.matchMedia ? window.matchMedia(query).matches : true),
        () => true,
    );
};
