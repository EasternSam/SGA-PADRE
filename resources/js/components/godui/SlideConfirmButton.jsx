import * as React from "react";
import {
  animate,
  motion,
  useMotionValue,
  useReducedMotion,
  useTransform,
} from "framer-motion";

const sizeMap = {
  sm: { track: "h-10 w-56", thumb: 32, pad: 4, text: "text-xs" },
  md: { track: "h-12 w-72", thumb: 40, pad: 4, text: "text-sm" },
  lg: { track: "h-14 w-80", thumb: 48, pad: 5, text: "text-base" },
};

const ArrowIcon = () => (
  <svg
    viewBox="0 0 24 24"
    fill="none"
    className="size-5"
    stroke="currentColor"
    strokeWidth="2.5"
    strokeLinecap="round"
    strokeLinejoin="round"
    aria-hidden="true"
  >
    <path d="M5 12h14M13 6l6 6-6 6" />
  </svg>
);

export const SlideConfirmButton = React.forwardRef(
  (
    {
      onConfirm,
      variant = "default",
      size = "md",
      threshold = 0.85,
      label = "Deslizar para confirmar",
      loadingLabel = "Procesando...",
      confirmedLabel = "Completado",
      disabled = false,
      className = "",
      ...props
    },
    forwardedRef
  ) => {
    const reduce = useReducedMotion() ?? false;
    const dims = sizeMap[size] || sizeMap.md;
    const trackRef = React.useRef(null);
    const [maxX, setMaxX] = React.useState(0);
    const [status, setStatus] = React.useState("idle");
    const statusRef = React.useRef(status);
    statusRef.current = status;

    const mounted = React.useRef(true);
    React.useEffect(() => {
      mounted.current = true;
      return () => {
        mounted.current = false;
      };
    }, []);

    const x = useMotionValue(0);
    const labelOpacity = useTransform(x, [0, Math.max(1, maxX * 0.6)], [1, 0]);
    const fillWidth = useTransform(x, (v) => v + dims.thumb);

    React.useLayoutEffect(() => {
      const el = trackRef.current;
      if (!el) return;
      const measure = () => {
        const usable = el.clientWidth - dims.thumb - dims.pad * 2;
        setMaxX(Math.max(0, usable));
      };
      measure();
      const ro = new ResizeObserver(measure);
      ro.observe(el);
      return () => ro.disconnect();
    }, [dims.thumb, dims.pad]);

    const settleBack = () => {
      animate(
        x,
        0,
        reduce
          ? { duration: 0.2 }
          : { type: "spring", stiffness: 500, damping: 40 }
      );
    };

    const confirm = async () => {
      animate(x, maxX, { type: "spring", stiffness: 500, damping: 40 });
      let result;
      try {
        result = onConfirm?.();
      } catch (err) {
        setStatus("idle");
        settleBack();
        return;
      }

      if (result && typeof result.then === "function") {
        setStatus("loading");
        try {
          await result;
        } catch {
          if (!mounted.current) return;
          setStatus("idle");
          settleBack();
          return;
        }
        if (!mounted.current) return;
      }
      setStatus("confirmed");
    };

    const handleDragEnd = () => {
      if (statusRef.current !== "idle") return;
      if (maxX > 0 && x.get() >= maxX * threshold) {
        confirm();
      } else {
        settleBack();
      }
    };

    const confirmed = status === "confirmed";
    const loading = status === "loading";
    const busy = confirmed || loading;
    const restingFootprint = dims.thumb + dims.pad * 2;

    const trackClasses =
      variant === "destructive"
        ? "border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/30"
        : "border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-[#171717]";

    const fillClasses =
      variant === "destructive"
        ? "bg-red-500/20"
        : "bg-brand/20";

    const thumbClasses =
      variant === "destructive"
        ? "bg-red-600 text-white"
        : "bg-brand hover:bg-brand-dark text-white";

    return (
      <div
        ref={forwardedRef}
        data-status={status}
        className={`relative isolate flex select-none items-center overflow-hidden rounded-full border [touch-action:none] transition-opacity duration-200 ${trackClasses} ${dims.track} ${dims.text} ${
          disabled ? "pointer-events-none opacity-50" : ""
        } ${className}`}
        {...props}
      >
        <div ref={trackRef} className="absolute inset-0" aria-hidden="true" />

        {/* Fill trailing the thumb */}
        <motion.div
          aria-hidden="true"
          className={`absolute rounded-full ${fillClasses}`}
          style={{
            top: dims.pad,
            bottom: dims.pad,
            left: dims.pad,
            width: fillWidth,
          }}
        />

        {/* Idle label */}
        <motion.span
          style={{
            opacity: busy ? 0 : labelOpacity,
            paddingLeft: restingFootprint,
            paddingRight: restingFootprint,
          }}
          className="pointer-events-none absolute inset-0 z-10 flex items-center justify-center whitespace-nowrap font-medium text-gray-500 dark:text-gray-400"
        >
          {label}
        </motion.span>

        {busy && (
          <span
            style={{
              paddingLeft: restingFootprint,
              paddingRight: restingFootprint,
            }}
            className="pointer-events-none absolute inset-0 z-10 flex items-center justify-center whitespace-nowrap font-medium text-gray-900 dark:text-gray-100"
          >
            {loading ? loadingLabel : confirmedLabel}
          </span>
        )}

        {/* Draggable thumb */}
        <motion.button
          type="button"
          aria-label={typeof label === "string" ? label : "Slide to confirm"}
          disabled={disabled}
          drag={busy || disabled ? false : "x"}
          dragConstraints={{ left: 0, right: maxX }}
          dragElastic={0.04}
          dragMomentum={false}
          onDragEnd={handleDragEnd}
          style={{ x, width: dims.thumb, height: dims.thumb, margin: dims.pad }}
          className={`relative z-20 inline-flex shrink-0 cursor-grab items-center justify-center rounded-full shadow-md active:cursor-grabbing focus:outline-none focus-visible:ring-2 focus-visible:ring-brand ${thumbClasses}`}
        >
          {loading ? (
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2.5"
              strokeLinecap="round"
              className="size-5 animate-spin"
              aria-hidden="true"
            >
              <path d="M21 12a9 9 0 1 1-6.2-8.6" />
            </svg>
          ) : confirmed ? (
            <svg
              viewBox="0 0 24 24"
              fill="none"
              className="size-5"
              aria-hidden="true"
            >
              <motion.path
                d="M5 12.5 10 17.5 19 7"
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                initial={{ pathLength: 0 }}
                animate={{ pathLength: 1 }}
                transition={{ duration: 0.3 }}
              />
            </svg>
          ) : (
            <ArrowIcon />
          )}
        </motion.button>
      </div>
    );
  }
);

SlideConfirmButton.displayName = "SlideConfirmButton";
export default SlideConfirmButton;
