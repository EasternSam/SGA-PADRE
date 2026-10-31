import * as React from "react";

const variantDefaults = {
  primary: {
    background: "var(--brand-primary, #211170)",
    shimmerColor: "rgba(255, 255, 255, 0.4)",
    shimmerSpread: "65deg",
    textClass: "text-white font-medium",
    borderClass: "border-brand-primary/30",
  },
  secondary: {
    background: "rgba(255, 255, 255, 0.08)",
    shimmerColor: "#e2e8f0",
    shimmerSpread: "90deg",
    textClass: "text-gray-900 dark:text-gray-100 font-medium",
    borderClass: "border-gray-300 dark:border-gray-700",
  },
  outline: {
    background: "transparent",
    shimmerColor: "var(--brand-primary, #211170)",
    shimmerSpread: "90deg",
    textClass: "text-gray-800 dark:text-gray-200 font-medium",
    borderClass: "border-gray-300 dark:border-gray-700",
  },
};

export const ShimmerButton = React.forwardRef(
  (
    {
      className = "",
      variant = "primary",
      size = "md",
      shimmer = true,
      shimmerColor,
      shimmerSize = "0.05em",
      shimmerDuration = "3s",
      borderRadius = "0.625rem",
      background,
      children,
      type = "button",
      onMouseEnter,
      onMouseLeave,
      disabled,
      ...props
    },
    ref
  ) => {
    const defaults = variantDefaults[variant] || variantDefaults.primary;
    const sparkRef = React.useRef(null);

    const sizeClasses = {
      sm: "px-3 py-1.5 text-xs",
      md: "px-4 py-2 text-sm",
      lg: "px-6 py-3 text-base",
    };

    const setSparkRate = (rate) => {
      sparkRef.current?.getAnimations({ subtree: true }).forEach((a) => {
        a.playbackRate = rate;
      });
    };

    const handleMouseEnter = (e) => {
      setSparkRate(2.5);
      onMouseEnter?.(e);
    };

    const handleMouseLeave = (e) => {
      setSparkRate(1);
      onMouseLeave?.(e);
    };

    const effectiveBg = background ?? defaults.background;
    const effectiveShimmer = shimmerColor ?? defaults.shimmerColor;

    return (
      <button
        ref={ref}
        type={type}
        disabled={disabled}
        className={`group relative z-0 inline-flex cursor-pointer items-center justify-center gap-2 overflow-hidden border whitespace-nowrap transform-gpu transition-all duration-300 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:pointer-events-none disabled:opacity-50 ${sizeClasses[size] || sizeClasses.md} ${defaults.textClass} ${defaults.borderClass} ${className}`}
        style={{
          borderRadius,
          background: effectiveBg,
          "--speed": shimmerDuration,
        }}
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
        {...props}
      >
        {shimmer && !disabled && (
          <div
            ref={sparkRef}
            className="absolute inset-0 -z-30 overflow-visible blur-[1px] [container-type:size]"
            aria-hidden="true"
          >
            <div className="animate-shimmer-slide absolute inset-0 aspect-square h-[100cqh] rounded-none motion-reduce:animate-none">
              <div
                className="animate-spin-around absolute -inset-full w-auto rotate-0 [background:conic-gradient(from_calc(270deg-(var(--spread,65deg)*0.5)),transparent_0,var(--shimmer-color)_var(--spread,65deg),transparent_var(--spread,65deg))] motion-reduce:animate-none"
                style={{
                  "--shimmer-color": effectiveShimmer,
                  "--spread": defaults.shimmerSpread,
                }}
              />
            </div>
          </div>
        )}

        <span className="relative z-10 flex items-center gap-2">{children}</span>

        {shimmer && !disabled && (
          <div
            className="absolute -z-20 [inset:0.06em] [background:var(--btn-inner-bg)]"
            style={{
              borderRadius: `calc(${borderRadius} - 1px)`,
              "--btn-inner-bg": effectiveBg,
            }}
            aria-hidden="true"
          />
        )}
      </button>
    );
  }
);

ShimmerButton.displayName = "ShimmerButton";
export default ShimmerButton;
