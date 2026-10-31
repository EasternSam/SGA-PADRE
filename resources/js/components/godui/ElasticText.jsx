import React, { forwardRef, useRef, useMemo, useCallback, useEffect, useLayoutEffect } from "react";
import {
  animate,
  motion,
  useMotionValue,
  useReducedMotion,
  useSpring,
  useTransform,
} from "framer-motion";
import { clamp, getTextContent, lerp } from "./text-utils";

const SPRING = { stiffness: 150, damping: 18, mass: 1 };
const AUTO_SPREAD = 2.5;
const VIEW_THRESHOLD = 0.3;

const CONTAINER_CLASS =
  "inline-block font-sans leading-[1.1] text-inherit [font-optical-sizing:auto] [font-variation-settings:'wght'_400]";
const SEGMENT_CLASS =
  "inline-block whitespace-pre [font-variation-settings:'wght'_var(--et-wght,400)] [will-change:font-variation-settings] motion-reduce:[will-change:auto]";

function Segment({
  segment,
  index,
  minWeight,
  maxWeight,
  reducedMotion,
  mode,
  spotlight,
  pointerX,
  pointerActive,
  getCenter,
  radius,
}) {
  const autoWeight = useTransform(spotlight, (position) => {
    const distance = Math.abs(index - position);
    const influence = clamp(1 - distance / AUTO_SPREAD, 0, 1);
    return lerp(minWeight, maxWeight, influence);
  });

  const hoverWeight = useTransform([pointerX, pointerActive], (latest) => {
    const [x, active] = latest;
    if (!active) {
      return minWeight;
    }
    const distance = Math.abs(x - getCenter(index));
    const influence = clamp(1 - distance / radius, 0, 1);
    return lerp(minWeight, maxWeight, influence);
  });

  const rawWeight = mode === "hover" ? hoverWeight : autoWeight;
  const weight = useSpring(rawWeight, SPRING);

  if (reducedMotion) {
    return (
      <span
        className={SEGMENT_CLASS}
        data-elastic-segment=""
        style={{ "--et-wght": minWeight, fontWeight: minWeight }}
      >
        {segment}
      </span>
    );
  }

  return (
    <motion.span
      className={SEGMENT_CLASS}
      data-elastic-segment=""
      style={{ "--et-wght": weight, fontWeight: weight }}
      aria-hidden={segment.trim() === "" ? true : undefined}
    >
      {segment}
    </motion.span>
  );
}

export const ElasticText = forwardRef(
  (
    {
      children,
      className,
      mode = "auto",
      minWeight = 400,
      maxWeight = 900,
      duration = 2.5,
      loop = true,
      startOnView = true,
      radius = 120,
      ...props
    },
    ref
  ) => {
    const reducedMotion = useReducedMotion() ?? false;
    const containerRef = useRef(null);
    const mergedRef = useCallback(
      (node) => {
        containerRef.current = node;
        if (typeof ref === "function") {
          ref(node);
        } else if (ref) {
          ref.current = node;
        }
      },
      [ref]
    );

    const textContent = getTextContent(children);
    const segments = useMemo(
      () => (textContent ? [...textContent] : null),
      [textContent]
    );

    const spotlight = useMotionValue(-AUTO_SPREAD);
    const pointerX = useMotionValue(0);
    const pointerActive = useMotionValue(0);
    const centersRef = useRef([]);
    const getCenter = useCallback(
      (index) => centersRef.current[index] ?? 0,
      []
    );

    useEffect(() => {
      if (reducedMotion || mode !== "auto" || !segments) {
        return;
      }
      const last = Math.max(segments.length - 1, 1);
      spotlight.set(-AUTO_SPREAD);

      const start = () =>
        loop
          ? animate(spotlight, [0, last], {
              duration,
              repeat: Number.POSITIVE_INFINITY,
              repeatType: "mirror",
              ease: "easeInOut",
            })
          : animate(spotlight, [-AUTO_SPREAD, last + AUTO_SPREAD], {
              duration,
              ease: "easeInOut",
            });

      const node = containerRef.current;
      if (
        !startOnView ||
        !node ||
        typeof IntersectionObserver === "undefined"
      ) {
        const controls = start();
        return () => controls.stop();
      }

      let controls;
      const observer = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            if (entry.isIntersecting) {
              controls = start();
              observer.disconnect();
              break;
            }
          }
        },
        { threshold: VIEW_THRESHOLD }
      );
      observer.observe(node);
      return () => {
        observer.disconnect();
        controls?.stop();
      };
    }, [duration, loop, mode, reducedMotion, segments, spotlight, startOnView]);

    const updateCenters = useCallback(() => {
      const container = containerRef.current;
      if (!container) {
        return;
      }
      const spans = container.querySelectorAll("[data-elastic-segment]");
      centersRef.current = Array.from(spans).map((span) => {
        const rect = span.getBoundingClientRect();
        return rect.left + rect.width / 2;
      });
    }, []);

    useLayoutEffect(() => {
      if (mode !== "hover") {
        return;
      }
      updateCenters();
      if (typeof window === "undefined") {
        return;
      }
      window.addEventListener("resize", updateCenters);
      return () => window.removeEventListener("resize", updateCenters);
    }, [mode, updateCenters]);

    const handleMouseMove = useCallback(
      (event) => {
        if (mode !== "hover") {
          return;
        }
        pointerX.set(event.clientX);
        updateCenters();
      },
      [mode, pointerX, updateCenters]
    );

    const interactionProps =
      mode === "hover" && !reducedMotion
        ? {
            onMouseEnter: () => pointerActive.set(1),
            onMouseLeave: () => pointerActive.set(0),
            onMouseMove: handleMouseMove,
          }
        : undefined;

    if (!segments) {
      return (
        <span
          ref={mergedRef}
          data-slot="elastic-text"
          className={`${CONTAINER_CLASS} ${className ?? ""}`}
          style={{ "--et-wght": minWeight, fontWeight: minWeight }}
          {...props}
        >
          {children}
        </span>
      );
    }

    return (
      <span
        ref={mergedRef}
        data-slot="elastic-text"
        className={`${CONTAINER_CLASS} ${className ?? ""}`}
        {...interactionProps}
        {...props}
      >
        {segments.map((segment, index) => (
          <Segment
            key={index}
            segment={segment}
            index={index}
            minWeight={minWeight}
            maxWeight={maxWeight}
            reducedMotion={reducedMotion}
            mode={mode}
            spotlight={spotlight}
            pointerX={pointerX}
            pointerActive={pointerActive}
            getCenter={getCenter}
            radius={radius}
          />
        ))}
      </span>
    );
  }
);

ElasticText.displayName = "ElasticText";
export default ElasticText;
