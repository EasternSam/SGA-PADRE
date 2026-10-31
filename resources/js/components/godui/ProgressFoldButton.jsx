"use client";

import * as React from "react";

const BUTTON_BASE =
  "group relative inline-flex cursor-pointer select-none whitespace-nowrap border-none p-0 bg-border font-medium [perspective:800px] [transform-style:preserve-3d] [-webkit-tap-highlight-color:transparent] focus-visible:[outline:2px_solid_var(--ring)] focus-visible:[outline-offset:4px] disabled:cursor-not-allowed disabled:opacity-50 disabled:pointer-events-none";

const LAYERS_BASE = "absolute inset-0 -z-[1] overflow-hidden";

const FRONT_BASE =
  "relative grid w-full place-items-center rounded-[inherit] [transform:rotateX(0deg)] [transform-origin:top_center] [transition:transform_0.2s_ease,background-color_0.15s_ease] group-hover:[transform:rotateX(32deg)] group-focus-visible:[transform:rotateX(32deg)] group-data-[status=loading]:[transform:rotateX(32deg)] motion-reduce:[transition:none]";

const BAR_BASE =
  "absolute top-0 left-0 h-full w-full origin-left [transform:scaleX(0)] group-data-[determinate=true]:[transform:scaleX(var(--progress-fold-fill,0))] group-[&[data-determinate=true][data-armed=true]]:[transition:transform_0.25s_ease] group-[&[data-status=loading]:not([data-determinate=true])]:w-[40%] group-[&[data-status=loading]:not([data-determinate=true])]:animate-progress-fold-indeterminate motion-reduce:group-[&[data-determinate=true][data-armed=true]]:[transition:none] motion-reduce:group-[&[data-status=loading]:not([data-determinate=true])]:animate-none";

const radiusClasses = {
  sm: "rounded-[var(--button-radius-sm,0.5rem)]",
  md: "rounded-[var(--button-radius-md,0.625rem)]",
  lg: "rounded-[var(--button-radius-lg,0.75rem)]",
};

const frontSizeClasses = {
  sm: "px-3 py-1.5 text-xs font-semibold leading-tight",
  md: "px-4 py-2.5 text-sm font-semibold leading-normal",
  lg: "px-6 py-3.5 text-base font-bold leading-normal",
};

const frontClasses = {
  primary: "bg-brand-primary hover:bg-[#082260] dark:bg-blue-600 dark:hover:bg-blue-500 text-white",
  secondary: "bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200/80 dark:border-slate-700/80 hover:bg-slate-50 dark:hover:bg-slate-700/60",
  destructive: "bg-red-600 hover:bg-red-700 text-white",
  success: "bg-emerald-600 hover:bg-emerald-700 text-white",
  outline: "bg-transparent text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800",
};

const backClasses = {
  primary: "bg-brand-primary/20 dark:bg-blue-600/20",
  secondary: "bg-slate-300/30 dark:bg-slate-700/40",
  destructive: "bg-red-600/20",
  success: "bg-emerald-600/20",
  outline: "bg-slate-200/20",
};

const barClasses = {
  primary: "bg-brand-primary dark:bg-blue-400",
  secondary: "bg-slate-500/50 dark:bg-slate-400/50",
  destructive: "bg-red-600",
  success: "bg-emerald-600",
  outline: "bg-slate-600",
};

const clampPercent = (value) => Math.max(0, Math.min(100, value));

export const ProgressFoldButton = React.forwardRef(
  (
    {
      className,
      style,
      children,
      variant = "primary",
      size = "md",
      status = "idle",
      progress,
      type = "button",
      ...props
    },
    ref
  ) => {
    const isLoading = status === "loading";
    const isDeterminate = isLoading && progress != null;
    const clamped = progress != null ? clampPercent(progress) : undefined;

    const [armed, setArmed] = React.useState(false);
    React.useEffect(() => {
      if (!isLoading) {
        setArmed(false);
        return;
      }
      setArmed(false);
      const id = requestAnimationFrame(() => setArmed(true));
      return () => cancelAnimationFrame(id);
    }, [isLoading]);

    return (
      <button
        ref={ref}
        type={type}
        data-variant={variant}
        data-size={size}
        data-status={isLoading ? "loading" : undefined}
        data-determinate={isDeterminate ? "true" : undefined}
        data-armed={armed ? "true" : undefined}
        aria-busy={isLoading || undefined}
        className={`${BUTTON_BASE} ${radiusClasses[size] || radiusClasses.md} ${className ?? ""}`}
        style={
          clamped != null
            ? {
                ...style,
                "--progress-fold-fill": clamped / 100,
              }
            : style
        }
        {...props}
      >
        <span
          className={`${LAYERS_BASE} ${radiusClasses[size] || radiusClasses.md}`}
          aria-hidden="true"
        >
          <span className={`absolute inset-0 ${backClasses[variant] || backClasses.primary}`} />
          <span className={`${BAR_BASE} ${barClasses[variant] || barClasses.primary}`} />
        </span>
        <span
          data-slot="progress-fold-front"
          className={`${FRONT_BASE} ${frontSizeClasses[size] || frontSizeClasses.md} ${
            frontClasses[variant] || frontClasses.primary
          }`}
        >
          {children}
        </span>
        {isLoading ? (
          <span
            className="sr-only"
            role="progressbar"
            aria-valuemin={0}
            aria-valuemax={100}
            aria-valuenow={isDeterminate ? clamped : undefined}
            aria-valuetext={isDeterminate ? `${clamped}%` : "Cargando..."}
          />
        ) : null}
      </button>
    );
  }
);

ProgressFoldButton.displayName = "ProgressFoldButton";
export default ProgressFoldButton;
