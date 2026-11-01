"use client";
import { useEffect, useId, useLayoutEffect, useMemo, useRef, useState } from "react";
import { AnimatePresence, animate, motion, useIsPresent, useMotionValue, useReducedMotion } from "motion/react";
import { Bell, Check, CheckCheck, CircleCheck, CircleDot, MessageCircle, TriangleAlert, X } from "lucide-react";
import * as PopoverPrimitive from "@radix-ui/react-popover";
import { Avatar } from "@/components/arc/avatar/avatar";
import { motionTokens } from "@/lib/motion-tokens";
import styles from "./notification-center.module.css";
const defaultLabels = {
  all: "Todas",
  unread: "No le\xEDdas",
  markAllRead: "Marcar le\xEDdas",
  markRead: "Marcar le\xEDda",
  markUnread: "Marcar no le\xEDda",
  dismiss: "Descartar",
  clearRead: "Limpiar le\xEDdas",
  emptyAllTitle: "Bandeja vac\xEDa",
  emptyAllDescription: "No hay notificaciones en este momento.",
  emptyUnreadTitle: "Est\xE1s al d\xEDa",
  emptyUnreadDescription: "Has revisado todas las actualizaciones.",
  viewAll: "Ver todas las alertas"
};
const enter = { duration: motionTokens.duration.standard, ease: [...motionTokens.ease.enter] };
const exitFast = { duration: motionTokens.duration.fast, ease: [...motionTokens.ease.standard] };
const instant = { duration: 0 };
const textIn = { opacity: 0, y: "0.3em", filter: `blur(${motionTokens.blur.soft}px)` };
const textOut = { opacity: 0, y: "-0.3em", filter: `blur(${motionTokens.blur.subtle}px)`, transition: exitFast };
const iconIn = { opacity: 0, scale: 0.6, filter: `blur(${motionTokens.blur.subtle}px)` };
const shown = { opacity: 1, y: "0em", scale: 1, filter: "blur(0px)" };
const fadeOut = { opacity: 0, transition: { duration: motionTokens.duration.instant } };
const cascade = (index) => Math.min(index * motionTokens.stagger.item, 0.2);
function Swap(props) {
  const present = useIsPresent();
  return <motion.span {...props} aria-hidden={present ? props["aria-hidden"] : true} />;
}
function SwapText({ children, reduce }) {
  return <span className={styles.swap}>
      <AnimatePresence mode="popLayout" initial={false}>
        <Swap key={children} className={styles.swapLine} initial={reduce ? { opacity: 0 } : textIn} animate={shown} exit={reduce ? fadeOut : textOut} transition={reduce ? instant : enter}>
          {children}
        </Swap>
      </AnimatePresence>
    </span>;
}
const rollVariants = {
  enter: (direction) => ({ opacity: 0, y: direction >= 0 ? "0.7em" : "-0.7em", filter: `blur(${motionTokens.blur.subtle}px)` }),
  center: { opacity: 1, y: "0em", filter: "blur(0px)" },
  exit: (direction) => ({ opacity: 0, y: direction >= 0 ? "-0.7em" : "0.7em", filter: `blur(${motionTokens.blur.subtle}px)`, transition: exitFast })
};
function RollingCount({ value, display = String(value), reduce }) {
  const [previous, setPrevious] = useState(value);
  const [direction, setDirection] = useState(0);
  if (value !== previous) {
    setDirection(value > previous ? 1 : -1);
    setPrevious(value);
  }
  return <span className={styles.roll}>
      <AnimatePresence mode="popLayout" initial={false} custom={direction}>
        <Swap
    key={display}
    className={styles.rollValue}
    custom={direction}
    variants={reduce ? void 0 : rollVariants}
    initial={reduce ? { opacity: 0 } : "enter"}
    animate={reduce ? { opacity: 1 } : "center"}
    exit={reduce ? fadeOut : "exit"}
    transition={reduce ? instant : { y: motionTokens.spring.snappy, opacity: exitFast, filter: exitFast }}
  >
          {display}
        </Swap>
      </AnimatePresence>
    </span>;
}
function MorphWidth({ reduce, morphKey, children }) {
  const frame = useRef(null);
  const content = useRef(null);
  const width = useMotionValue("auto");
  const changedAt = useRef(0);
  useLayoutEffect(() => {
    changedAt.current = performance.now();
  }, [morphKey]);
  useEffect(() => {
    const node = content.current;
    if (!node || typeof ResizeObserver === "undefined") return;
    let last;
    let controls;
    const settle = () => {
      width.jump("auto");
      if (frame.current) frame.current.style.width = "auto";
    };
    const observer = new ResizeObserver(([entry]) => {
      const next = entry.borderBoxSize?.[0]?.inlineSize ?? node.offsetWidth;
      const current = width.get();
      const from = typeof current === "number" ? current : last;
      last = next;
      controls?.stop();
      if (reduce || from === void 0 || from === next || performance.now() - changedAt.current > 120) return settle();
      if (frame.current) frame.current.style.width = `${from}px`;
      controls = animate(width, [from, next], { ...motionTokens.spring.morph, onComplete: settle });
    });
    observer.observe(node);
    return () => {
      observer.disconnect();
      controls?.stop();
    };
  }, [width, reduce]);
  return <motion.span ref={frame} className={styles.morph} style={{ width }}>
      <span ref={content} className={styles.morphContent}>{children}</span>
    </motion.span>;
}
function NotificationVisual({ item }) {
  if (item.actor) return <Avatar name={item.actor.name} src={item.actor.photo} size="md" />;
  return <span className={[styles.eventIcon, styles[item.tone ?? "info"]].join(" ")} aria-hidden="true">
      {item.tone === "warning" ? <TriangleAlert size={18} strokeWidth={1.7} /> : item.tone === "success" ? <CircleCheck size={18} strokeWidth={1.7} /> : <MessageCircle size={18} strokeWidth={1.7} />}
    </span>;
}
export function NotificationCenter({
  notifications: initial,
  label = "Notificaciones",
  labels,
  onReadChange,
  onDismiss,
  open,
  onOpenChange,
  avoidCollisions = true
}) {
  const l = { ...defaultLabels, ...labels };
  const reduce = useReducedMotion();
  const layoutId = useId();
  const [internalOpen, setInternalOpen] = useState(false);
  const [items, setItems] = useState(initial);
  const [view, setView] = useState("all");
  const [expandedId, setExpandedId] = useState(null);
  const [bulk, setBulk] = useState(false);
  const itemRefs = useRef(/* @__PURE__ */ new Map());
  const allTabRef = useRef(null);
  const unreadTabRef = useRef(null);
  const isOpen = open ?? internalOpen;
  useEffect(() => {
    setItems(initial);
  }, [initial]);
  const unreadCount = useMemo(() => items.filter((item) => !item.read).length, [items]);
  const readCount = items.length - unreadCount;
  const visible = view === "unread" ? items.filter((item) => !item.read) : items;
  const summary = unreadCount ? `${unreadCount} ${unreadCount === 1 ? "alerta pendiente" : "alertas pendientes"}` : "Est\xE1s al d\xEDa";
  function setOpen(next) {
    if (open === void 0) setInternalOpen(next);
    onOpenChange?.(next);
  }
  function toggleRead(item) {
    const next = !item.read;
    if (next && view === "unread") {
      const index = visible.findIndex((entry) => entry.id === item.id);
      const nextId = visible[index + 1]?.id ?? visible[index - 1]?.id;
      requestAnimationFrame(() => nextId ? itemRefs.current.get(nextId)?.focus() : unreadTabRef.current?.focus());
    }
    setBulk(false);
    setItems((current) => current.map((entry) => entry.id === item.id ? { ...entry, read: next } : entry));
    if (next && view === "unread") setExpandedId(null);
    onReadChange?.(item, next);
  }
  function markAllRead() {
    items.filter((item) => !item.read).forEach((item) => onReadChange?.(item, true));
    setBulk(true);
    setItems((current) => current.map((item) => ({ ...item, read: true })));
    setExpandedId(null);
    requestAnimationFrame(() => (view === "unread" ? unreadTabRef : allTabRef).current?.focus());
  }
  function dismiss(item) {
    const index = visible.findIndex((entry) => entry.id === item.id);
    const nextId = visible[index + 1]?.id ?? visible[index - 1]?.id;
    requestAnimationFrame(() => nextId ? itemRefs.current.get(nextId)?.focus() : unreadTabRef.current?.focus());
    setBulk(false);
    setItems((current) => current.filter((entry) => entry.id !== item.id));
    if (expandedId === item.id) setExpandedId(null);
    onDismiss?.(item);
  }
  function clearRead() {
    items.filter((item) => item.read).forEach((item) => onDismiss?.(item));
    setBulk(true);
    setItems((current) => current.filter((item) => !item.read));
    requestAnimationFrame(() => allTabRef.current?.focus());
  }
  const height = reduce ? instant : { height: motionTokens.spring.smooth, opacity: enter };
  return <PopoverPrimitive.Root open={isOpen} onOpenChange={setOpen}>
      <PopoverPrimitive.Trigger asChild>
        {
    /* The trigger anchors the panel, giving press feedback without layout shift */
  }
        <button
    className={styles.trigger}
    type="button"
    aria-label={`${label}${unreadCount ? `, ${unreadCount} no le\xEDdas` : ""}`}
  >
          <motion.span
    animate={{ rotate: isOpen && !reduce ? -12 : 0 }}
    transition={reduce ? instant : motionTokens.spring.snappy}
  >
            <Bell size={18} strokeWidth={1.8} aria-hidden="true" />
          </motion.span>
          <AnimatePresence initial={false}>
            {unreadCount > 0 && <motion.span
    key="badge"
    className={styles.badge}
    aria-hidden="true"
    initial={reduce ? { opacity: 0 } : { opacity: 0, scale: 0.6 }}
    animate={{ opacity: 1, scale: 1 }}
    exit={reduce ? fadeOut : { opacity: 0, scale: 0.6, transition: exitFast }}
    transition={reduce ? instant : motionTokens.spring.snappy}
  >
                <RollingCount value={unreadCount} display={unreadCount > 9 ? "9+" : String(unreadCount)} reduce={reduce} />
              </motion.span>}
          </AnimatePresence>
        </button>
      </PopoverPrimitive.Trigger>
      <PopoverPrimitive.Portal>
        <PopoverPrimitive.Content
    className={styles.panel}
    align="end"
    side="bottom"
    sideOffset={10}
    collisionPadding={16}
    avoidCollisions={avoidCollisions}
    aria-label={label}
  >
          <div className={styles.header}>
            <div>
              <div className={styles.heading}>
                <h2>{label}</h2>
                <span className={styles.count}>
                  <RollingCount value={unreadCount} reduce={reduce} />
                </span>
              </div>
              <p aria-live="polite">
                <SwapText reduce={reduce}>{summary}</SwapText>
              </p>
            </div>
            <PopoverPrimitive.Close className={styles.close} aria-label="Cerrar notificaciones">
              <X size={17} strokeWidth={1.75} aria-hidden="true" />
            </PopoverPrimitive.Close>
          </div>

          <div className={styles.toolbar}>
            <div className={styles.viewSwitch} role="group" aria-label="Filtrar notificaciones">
              {["all", "unread"].map((next) => <button
    key={next}
    ref={next === "unread" ? unreadTabRef : allTabRef}
    type="button"
    className={view === next ? styles.viewActive : void 0}
    aria-pressed={view === next}
    onClick={() => {
      setBulk(false);
      setView(next);
      setExpandedId(null);
    }}
  >
                  {view === next && <motion.span
    className={styles.viewHighlight}
    layoutId={`${layoutId}-view`}
    transition={reduce ? instant : motionTokens.spring.morph}
  />}
                  <span>{next === "all" ? l.all : l.unread}</span>
                </button>)}
            </div>
            <AnimatePresence initial={false}>
              {unreadCount > 0 && <motion.button
    key="mark-all"
    type="button"
    className={styles.markAll}
    onClick={markAllRead}
    initial={reduce ? { opacity: 0 } : { opacity: 0, filter: `blur(${motionTokens.blur.subtle}px)` }}
    animate={{ opacity: 1, filter: "blur(0px)" }}
    exit={reduce ? fadeOut : { opacity: 0, filter: `blur(${motionTokens.blur.subtle}px)`, transition: exitFast }}
    transition={reduce ? instant : enter}
  >
                  <CheckCheck size={15} strokeWidth={1.75} aria-hidden="true" />
                  <span>{l.markAllRead}</span>
                </motion.button>}
            </AnimatePresence>
          </div>

          {
    /* Rows collapse their own height on exit */
  }
          <div className={styles.list} role="list" aria-label={view === "all" ? "Todas las notificaciones" : "Notificaciones no le\xEDdas"}>
            <AnimatePresence initial={false} custom={bulk}>
              {visible.map((item, index) => <motion.div
    key={item.id}
    role="listitem"
    className={styles.row}
    custom={bulk}
    variants={{
      exit: (isBulk) => reduce ? fadeOut : {
        height: 0,
        opacity: 0,
        transition: {
          height: { ...motionTokens.spring.smooth, delay: isBulk ? cascade(index) : 0 },
          opacity: { ...exitFast, delay: isBulk ? cascade(index) : 0 }
        }
      }
    }}
    initial={reduce ? { opacity: 0 } : { height: 0 }}
    animate={{ height: "auto", opacity: 1 }}
    exit="exit"
    transition={height}
  >
                  <article
    className={[
      styles.item,
      item.read ? styles.itemRead : "",
      expandedId === item.id ? styles.itemExpanded : ""
    ].filter(Boolean).join(" ")}
    style={{ "--index": Math.min(index, 7) }}
  >
                    <div className={styles.itemMain}>
                      <NotificationVisual item={item} />
                      <button
    ref={(node) => {
      if (node) itemRefs.current.set(item.id, node);
      else itemRefs.current.delete(item.id);
    }}
    type="button"
    className={styles.itemToggle}
    aria-expanded={expandedId === item.id}
    aria-label={`${item.title}${item.read ? "" : ", no le\xEDda"}. ${expandedId === item.id ? "Ocultar detalles" : "Ver detalles"}`}
    onClick={() => setExpandedId((current) => current === item.id ? null : item.id)}
  >
                        <span className={styles.itemTitle}>
                          <strong>{item.title}</strong>
                          <AnimatePresence initial={false} custom={bulk}>
                            {!item.read && <motion.span
    key="dot"
    className={styles.unreadDot}
    aria-hidden="true"
    custom={bulk}
    variants={{
      exit: (isBulk) => reduce ? fadeOut : { opacity: 0, scale: 0.3, transition: { ...exitFast, delay: isBulk ? cascade(index) : 0 } }
    }}
    initial={reduce ? { opacity: 0 } : { opacity: 0, scale: 0.3 }}
    animate={{ opacity: 1, scale: 1 }}
    exit="exit"
    transition={reduce ? instant : motionTokens.spring.snappy}
  />}
                          </AnimatePresence>
                        </span>
                        <span className={styles.itemPreview}>
                          {item.description ?? (item.actor ? `De ${item.actor.name}` : "Ver detalles de la actualizaci\xF3n")}
                        </span>
                      </button>
                      <time className={styles.time}>{item.time}</time>
                    </div>
                    <AnimatePresence initial={false}>
                      {expandedId === item.id && <motion.div
    className={styles.details}
    initial={reduce ? { opacity: 0 } : { height: 0, opacity: 0 }}
    animate={{ height: "auto", opacity: 1 }}
    exit={reduce ? fadeOut : { height: 0, opacity: 0, transition: { height: motionTokens.spring.smooth, opacity: exitFast } }}
    transition={height}
  >
                          <p>{item.description ?? (item.actor ? `${item.actor.name} comparti\xF3 una actualizaci\xF3n.` : "Actualizaci\xF3n disponible para revisi\xF3n.")}</p>
                          <div className={styles.itemActions}>
                            {item.url && <button
    type="button"
    onClick={() => {
      if (!item.read) toggleRead(item);
      setOpen(false);
      window.location.href = item.url;
    }}
  >
                              <span>Abrir</span>
                            </button>}
                            <button type="button" onClick={() => toggleRead(item)}>
                              <MorphWidth reduce={reduce} morphKey={item.read ? "read" : "unread"}>
                                <span className={styles.actionIcon}>
                                  <AnimatePresence mode="popLayout" initial={false}>
                                    <Swap
    key={item.read ? "unread" : "read"}
    className={styles.actionGlyph}
    initial={reduce ? { opacity: 0 } : iconIn}
    animate={shown}
    exit={reduce ? fadeOut : { ...iconIn, transition: exitFast }}
    transition={reduce ? instant : motionTokens.spring.snappy}
  >
                                      {item.read ? <CircleDot size={14} strokeWidth={1.75} aria-hidden="true" /> : <Check size={14} strokeWidth={1.75} aria-hidden="true" />}
                                    </Swap>
                                  </AnimatePresence>
                                </span>
                                <SwapText reduce={reduce}>{item.read ? l.markUnread : l.markRead}</SwapText>
                              </MorphWidth>
                            </button>
                            <button type="button" onClick={() => dismiss(item)}>
                              <X size={14} strokeWidth={1.75} aria-hidden="true" />
                              {l.dismiss}
                            </button>
                          </div>
                        </motion.div>}
                    </AnimatePresence>
                  </article>
                </motion.div>)}
              {visible.length === 0 && <motion.div
    key="empty"
    className={styles.emptyFrame}
    initial={reduce ? { opacity: 0 } : { height: 0, opacity: 0 }}
    animate={{ height: "auto", opacity: 1 }}
    exit={reduce ? fadeOut : { height: 0, opacity: 0, transition: { height: motionTokens.spring.smooth, opacity: exitFast } }}
    transition={reduce ? instant : { height: { ...motionTokens.spring.smooth, delay: bulk ? cascade(3) : 0 }, opacity: { ...enter, delay: motionTokens.duration.fast } }}
  >
                  <motion.div
    className={styles.empty}
    initial={reduce ? false : { y: 6 }}
    animate={{ y: 0 }}
    transition={reduce ? instant : { ...enter, delay: motionTokens.duration.fast }}
  >
                    <CircleCheck size={26} strokeWidth={1.5} aria-hidden="true" />
                    <strong>
                      <SwapText reduce={reduce}>{view === "unread" ? l.emptyUnreadTitle : l.emptyAllTitle}</SwapText>
                    </strong>
                    <p>
                      <SwapText reduce={reduce}>{view === "unread" ? l.emptyUnreadDescription : l.emptyAllDescription}</SwapText>
                    </p>
                    {view === "unread" && items.length > 0 && <button
    type="button"
    onClick={() => {
      setBulk(false);
      setView("all");
    }}
  >
                        {l.viewAll}
                      </button>}
                  </motion.div>
                </motion.div>}
            </AnimatePresence>
          </div>

          <AnimatePresence initial={false}>
            {readCount > 0 && <motion.div
    key="footer"
    className={styles.footerFrame}
    initial={reduce ? { opacity: 0 } : { height: 0, opacity: 0 }}
    animate={{ height: "auto", opacity: 1 }}
    exit={reduce ? fadeOut : { height: 0, opacity: 0, transition: { height: motionTokens.spring.smooth, opacity: exitFast } }}
    transition={height}
  >
                <div className={styles.footer}>
                  <span>
                    <RollingCount value={readCount} reduce={reduce} /> {readCount === 1 ? "le\xEDda" : "le\xEDdas"}
                  </span>
                  <button type="button" onClick={clearRead}>
                    {l.clearRead}
                  </button>
                </div>
              </motion.div>}
          </AnimatePresence>
        </PopoverPrimitive.Content>
      </PopoverPrimitive.Portal>
    </PopoverPrimitive.Root>;
}
export default NotificationCenter;
