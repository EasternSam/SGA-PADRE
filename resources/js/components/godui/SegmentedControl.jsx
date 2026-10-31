"use client";

import * as React from "react";
import { motion, useReducedMotion } from "framer-motion";

const sizeClasses = {
  sm: "h-8 text-xs gap-1",
  md: "h-10 text-sm gap-1.5",
  lg: "h-12 text-base gap-2",
};

let pillSeed = 0;

export const SegmentedControl = React.forwardRef(
  (
    {
      options = [],
      value: valueProp,
      defaultValue,
      size = "md",
      onChange,
      className = "",
      ...props
    },
    ref
  ) => {
    const reduceMotion = useReducedMotion();
    const layoutId = React.useMemo(() => `segmented-pill-${pillSeed++}`, []);
    const isControlled = valueProp !== undefined;
    const [internal, setInternal] = React.useState(
      () => defaultValue ?? options[0]?.value
    );
    const value = isControlled ? valueProp : internal;

    const select = (next) => {
      if (!isControlled) setInternal(next);
      onChange?.(next);
    };

    return (
      <div
        ref={ref}
        role="tablist"
        className={`inline-flex items-center rounded-xl border border-gray-200/80 dark:border-gray-800 bg-gray-100/90 dark:bg-gray-900/70 p-1 shadow-inner ${
          sizeClasses[size] || sizeClasses.md
        } ${className}`}
        {...props}
      >
        {options.map((opt) => {
          const active = opt.value === value;
          return (
            <button
              key={opt.value}
              type="button"
              role="tab"
              aria-selected={active}
              disabled={opt.disabled}
              onClick={() => select(opt.value)}
              className={`relative isolate inline-flex h-full items-center justify-center gap-1.5 rounded-lg px-3 font-semibold text-xs sm:text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand active:scale-[0.97] disabled:pointer-events-none disabled:opacity-40 select-none cursor-pointer ${
                active
                  ? "text-brand font-bold"
                  : "text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
              }`}
            >
              {active && (
                <motion.span
                  layoutId={layoutId}
                  transition={
                    reduceMotion
                      ? { duration: 0 }
                      : { type: "spring", stiffness: 520, damping: 34 }
                  }
                  className="absolute inset-0 z-0 rounded-lg bg-white dark:bg-[#171717] shadow-sm border border-gray-200/60 dark:border-[#262626]"
                />
              )}
              {opt.icon && (
                <span className="relative z-10 shrink-0 text-current [&_svg]:size-4">
                  {opt.icon}
                </span>
              )}
              <span className="relative z-10 whitespace-nowrap">{opt.label}</span>
              {opt.badge !== undefined && (
                <span className="relative z-10 ml-1 rounded-full px-1.5 py-0.2 text-[10px] bg-brand/10 dark:bg-brand/20 text-brand font-bold">
                  {opt.badge}
                </span>
              )}
            </button>
          );
        })}
      </div>
    );
  }
);

SegmentedControl.displayName = "SegmentedControl";
export default SegmentedControl;
