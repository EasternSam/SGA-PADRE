import * as React from "react";
import {
  useInView,
  useMotionValue,
  useReducedMotion,
  useSpring,
} from "framer-motion";

export const NumberTicker = React.forwardRef(
  (
    {
      value = 0,
      startValue = 0,
      direction = "up",
      delay = 0,
      decimalPlaces = 0,
      damping = 60,
      stiffness = 100,
      prefix = "",
      suffix = "",
      className = "",
      ...props
    },
    forwardedRef
  ) => {
    const ref = React.useRef(null);
    const reduceMotion = useReducedMotion();
    const numVal = typeof value === "number" ? value : parseFloat(value) || 0;
    const motionValue = useMotionValue(
      direction === "down" ? numVal : startValue
    );
    const springValue = useSpring(motionValue, { damping, stiffness });
    const isInView = useInView(ref, { once: true, margin: "0px" });

    React.useImperativeHandle(forwardedRef, () => ref.current);

    React.useEffect(() => {
      if (!isInView) return;

      const target = direction === "down" ? startValue : numVal;
      const timer = setTimeout(() => {
        motionValue.set(target);
        if (reduceMotion) springValue.jump(target);
      }, delay * 1000);

      return () => clearTimeout(timer);
    }, [
      motionValue,
      springValue,
      isInView,
      delay,
      numVal,
      direction,
      startValue,
      reduceMotion,
    ]);

    React.useEffect(() => {
      const unsubscribe = springValue.on("change", (latest) => {
        if (ref.current) {
          const formatted = Intl.NumberFormat("es-DO", {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
          }).format(Number(latest.toFixed(decimalPlaces)));
          ref.current.textContent = `${prefix}${formatted}${suffix}`;
        }
      });
      return () => unsubscribe();
    }, [springValue, decimalPlaces, prefix, suffix]);

    const initialFormatted = Intl.NumberFormat("es-DO", {
      minimumFractionDigits: decimalPlaces,
      maximumFractionDigits: decimalPlaces,
    }).format(direction === "down" ? numVal : startValue);

    return (
      <span
        ref={ref}
        data-slot="number-ticker"
        className={`inline-block tabular-nums tracking-normal text-current ${className}`}
        {...props}
      >
        {prefix}{initialFormatted}{suffix}
      </span>
    );
  }
);

NumberTicker.displayName = "NumberTicker";
export default NumberTicker;
