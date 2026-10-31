import * as React from "react";
import { animate, motion, useMotionValue } from "framer-motion";

export const HoldConfirmButton = React.forwardRef(
  (
    {
      onConfirm,
      variant = "destructive",
      size = "md",
      duration = 900,
      holdingLabel = "Confirmando...",
      confirmedLabel = "Confirmado",
      className = "",
      children = "Mantener presionado",
      disabled,
      onKeyDown,
      onKeyUp,
      ...props
    },
    forwardedRef
  ) => {
    const [status, setStatus] = React.useState("idle");
    const statusRef = React.useRef(status);
    statusRef.current = status;

    const progress = useMotionValue(0);
    const playback = React.useRef(null);
    const resetTimer = React.useRef(null);
    const mounted = React.useRef(true);

    React.useEffect(() => {
      mounted.current = true;
      return () => {
        mounted.current = false;
        playback.current?.stop();
        clearTimeout(resetTimer.current);
      };
    }, []);

    const complete = () => {
      if (!mounted.current) return;
      setStatus("confirmed");
      onConfirm?.();
      resetTimer.current = setTimeout(() => {
        if (!mounted.current) return;
        setStatus("idle");
        animate(progress, 0, { duration: 0.2 });
      }, 1200);
    };

    const start = () => {
      if (statusRef.current !== "idle" || disabled) return;
      setStatus("holding");
      playback.current = animate(progress, 1, {
        duration: duration / 1000,
        ease: "linear",
        onComplete: complete,
      });
    };

    const cancel = () => {
      if (statusRef.current !== "holding") return;
      playback.current?.stop();
      setStatus("idle");
      animate(progress, 0, {
        type: "spring",
        stiffness: 320,
        damping: 32,
      });
    };

    const handleKeyDown = (event) => {
      onKeyDown?.(event);
      if ((event.key === " " || event.key === "Enter") && !event.repeat) {
        event.preventDefault();
        start();
      }
    };

    const handleKeyUp = (event) => {
      onKeyUp?.(event);
      if (event.key === " " || event.key === "Enter") {
        event.preventDefault();
        cancel();
      }
    };

    const variantStyles = {
      destructive: "bg-red-600 hover:bg-red-700 text-white",
      default: "bg-brand hover:bg-brand-dark text-white",
    };

    const sizeStyles = {
      sm: "px-3 py-1.5 text-xs rounded-lg",
      md: "px-4 py-2 text-sm rounded-lg",
      lg: "px-5 py-2.5 text-base rounded-xl",
    };

    return (
      <button
        ref={forwardedRef}
        type="button"
        disabled={disabled || status === "confirmed"}
        onMouseDown={start}
        onMouseUp={cancel}
        onMouseLeave={cancel}
        onTouchStart={start}
        onTouchEnd={cancel}
        onKeyDown={handleKeyDown}
        onKeyUp={handleKeyUp}
        className={`relative inline-flex cursor-pointer select-none items-center justify-center gap-2 overflow-hidden font-medium transition-transform active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50 ${
          variantStyles[variant] || variantStyles.destructive
        } ${sizeStyles[size] || sizeStyles.md} ${className}`}
        {...props}
      >
        <motion.div
          className="absolute inset-0 bg-black/25 origin-left"
          style={{ scaleX: progress }}
        />

        <span className="relative z-10 flex items-center gap-2">
          {status === "idle" && children}
          {status === "holding" && holdingLabel}
          {status === "confirmed" && (
            <>
              <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              {confirmedLabel}
            </>
          )}
        </span>
      </button>
    );
  }
);

HoldConfirmButton.displayName = "HoldConfirmButton";
export default HoldConfirmButton;
