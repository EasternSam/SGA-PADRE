import * as React from "react";

const ROOT_BASE =
  "group relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#161D30] text-gray-900 dark:text-gray-100 transition-all duration-300 shadow-sm hover:shadow-md";

const GLOW_BASE =
  "pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 ease-out group-hover:opacity-100 [background:radial-gradient(var(--spotlight-radius)_circle_at_var(--x,50%)_var(--y,50%),var(--spotlight-color),transparent_65%)]";

const BORDER_BASE =
  "pointer-events-none absolute inset-0 rounded-xl opacity-0 transition-opacity duration-500 ease-out group-hover:opacity-100 [background:radial-gradient(var(--spotlight-radius)_circle_at_var(--x,50%)_var(--y,50%),var(--spotlight-color),transparent_65%)] [-webkit-mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [-webkit-mask-composite:xor] [mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:exclude] p-px";

export const SpotlightCard = React.forwardRef(
  (
    {
      glowColor = "rgba(30, 58, 138, 0.18)",
      radius = 350,
      border = true,
      glowBackground = false,
      className = "",
      style,
      children,
      onPointerMove,
      ...props
    },
    forwardedRef,
  ) => {
    const ref = React.useRef(null);
    React.useImperativeHandle(forwardedRef, () => ref.current);

    const handlePointerMove = (e) => {
      const el = ref.current;
      if (el) {
        const rect = el.getBoundingClientRect();
        el.style.setProperty("--x", `${e.clientX - rect.left}px`);
        el.style.setProperty("--y", `${e.clientY - rect.top}px`);
      }
      onPointerMove?.(e);
    };

    return (
      <div
        ref={ref}
        data-slot="spotlight-card"
        onPointerMove={handlePointerMove}
        className={`${ROOT_BASE} ${className}`}
        style={{
          "--spotlight-color": glowColor,
          "--spotlight-radius": `${radius}px`,
          ...style,
        }}
        {...props}
      >
        {glowBackground && <div aria-hidden="true" className={GLOW_BASE} />}
        {border && <div aria-hidden="true" className={BORDER_BASE} />}
        <div className="relative z-10 w-full h-full">{children}</div>
      </div>
    );
  }
);

SpotlightCard.displayName = "SpotlightCard";
export default SpotlightCard;
