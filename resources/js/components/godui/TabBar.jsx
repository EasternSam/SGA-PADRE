"use client";

import * as React from "react";
import { motion, useReducedMotion } from "framer-motion";

let tabBarSeed = 0;

export const TabBar = React.forwardRef(
  (
    {
      tabs = [],
      value: valueProp,
      defaultValue,
      labelsOnActiveOnly = false,
      safeArea = false,
      onChange,
      className = "",
      ...props
    },
    ref
  ) => {
    const reduceMotion = useReducedMotion();
    const blobId = React.useMemo(() => `tab-bar-blob-${tabBarSeed++}`, []);
    const isControlled = valueProp !== undefined;
    const [internal, setInternal] = React.useState(
      () => defaultValue ?? tabs[0]?.value
    );
    const value = isControlled ? valueProp : internal;

    const select = (next) => {
      if (!isControlled) setInternal(next);
      onChange?.(next);
    };

    const spring = reduceMotion
      ? { duration: 0 }
      : { type: "spring", stiffness: 520, damping: 32 };

    return (
      <nav
        ref={ref}
        aria-label="Contextual navigation"
        className={`inline-flex items-center gap-1 rounded-full border border-gray-200/80 dark:border-white/10 bg-white/95 dark:bg-[#171717]/95 p-1.5 shadow-xl shadow-black/10 dark:shadow-black/50 backdrop-blur-xl transition-all ${
          safeArea ? "pb-[max(0.375rem,env(safe-area-inset-bottom))]" : ""
        } ${className}`}
        {...props}
      >
        {tabs.map((tab) => {
          const active = tab.value === value;
          return (
            <button
              key={tab.value}
              type="button"
              aria-label={tab.label}
              aria-current={active ? "page" : undefined}
              onClick={() => select(tab.value)}
              className={`relative inline-flex h-9 sm:h-10 items-center justify-center gap-2 rounded-full px-3.5 sm:px-4 font-semibold text-xs sm:text-sm outline-none transition-colors select-none focus-visible:ring-2 focus-visible:ring-brand cursor-pointer ${
                active
                  ? "text-white font-bold"
                  : "text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
              }`}
            >
              {active && (
                <motion.span
                  layoutId={blobId}
                  transition={spring}
                  className="absolute inset-0 rounded-full bg-brand shadow-md shadow-brand/30"
                />
              )}
              <motion.span
                className="relative flex h-4 w-4 sm:h-5 sm:w-5 items-center justify-center [&_svg]:size-4"
                animate={
                  reduceMotion || !active
                    ? { scale: 1 }
                    : { scale: [1, 1.18, 1] }
                }
                transition={{ duration: 0.3, ease: "easeOut" }}
              >
                {tab.icon}
                {tab.badge !== undefined && (
                  <span className="-right-2 -top-2 absolute flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 font-bold text-[9px] text-white ring-2 ring-white dark:ring-gray-900">
                    {tab.badge}
                  </span>
                )}
              </motion.span>
              {(!labelsOnActiveOnly || active) && (
                <motion.span
                  layout
                  initial={
                    labelsOnActiveOnly && !reduceMotion
                      ? { opacity: 0, width: 0 }
                      : false
                  }
                  animate={{ opacity: 1, width: "auto" }}
                  transition={spring}
                  className="relative overflow-hidden whitespace-nowrap text-xs font-bold"
                >
                  {tab.label}
                </motion.span>
              )}
            </button>
          );
        })}
      </nav>
    );
  }
);

TabBar.displayName = "TabBar";
export default TabBar;
