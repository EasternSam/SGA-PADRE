import * as React from "react"
import { motion, AnimatePresence } from "framer-motion"
import { cn } from "@/lib/utils"

const HighlightContext = React.createContext(null)

export function useHighlight() {
  const context = React.useContext(HighlightContext)
  return context
}

export function Highlight({
  children,
  className,
  containerClassName,
  style,
  transition = { type: "spring", stiffness: 380, damping: 30 },
  hover = true,
  click = false,
  enabled = true,
  exitDelay = 150,
  mode = "parent",
}) {
  const containerRef = React.useRef(null)
  const [activeValue, setActiveValue] = React.useState(null)
  const [bounds, setBounds] = React.useState(null)
  const [activeClassName, setActiveClassName] = React.useState("")
  const exitTimeoutRef = React.useRef(null)

  const clearBounds = React.useCallback(() => {
    if (exitTimeoutRef.current) clearTimeout(exitTimeoutRef.current)
    exitTimeoutRef.current = setTimeout(() => {
      setBounds(null)
      setActiveValue(null)
    }, exitDelay)
  }, [exitDelay])

  const safeSetBounds = React.useCallback((elementRect, itemClassName, id) => {
    if (exitTimeoutRef.current) {
      clearTimeout(exitTimeoutRef.current)
      exitTimeoutRef.current = null
    }
    if (!containerRef.current || !elementRect) return

    const containerRect = containerRef.current.getBoundingClientRect()
    const newBounds = {
      top: elementRect.top - containerRect.top,
      left: elementRect.left - containerRect.left,
      width: elementRect.width,
      height: elementRect.height,
    }

    setBounds(newBounds)
    if (itemClassName !== undefined) {
      setActiveClassName(itemClassName)
    }
    if (id !== undefined) {
      setActiveValue(id)
    }
  }, [])

  // Listen for scroll in container or subtrees to recalculate active element position
  React.useEffect(() => {
    const container = containerRef.current
    if (!container) return

    const handleScroll = () => {
      if (!activeValue) return
      const activeEl = container.querySelector(`[data-highlight-id="${activeValue}"]`)
      if (activeEl) {
        const rect = activeEl.getBoundingClientRect()
        const containerRect = container.getBoundingClientRect()
        setBounds({
          top: rect.top - containerRect.top,
          left: rect.left - containerRect.left,
          width: rect.width,
          height: rect.height,
        })
      }
    }

    container.addEventListener("scroll", handleScroll, { passive: true, capture: true })
    return () => container.removeEventListener("scroll", handleScroll, { capture: true })
  }, [activeValue])

  const contextValue = React.useMemo(() => ({
    mode,
    activeValue,
    setActiveValue,
    setBounds: safeSetBounds,
    clearBounds,
    hover,
    click,
    enabled,
    activeClassName,
    setActiveClassName,
  }), [mode, activeValue, safeSetBounds, clearBounds, hover, click, enabled, activeClassName])

  if (!enabled) return children

  return (
    <HighlightContext.Provider value={contextValue}>
      <div
        ref={containerRef}
        data-slot="motion-highlight-container"
        className={cn("relative", containerClassName)}
      >
        <AnimatePresence initial={false}>
          {bounds && (
            <motion.div
              data-slot="motion-highlight"
              initial={{
                top: bounds.top,
                left: bounds.left,
                width: bounds.width,
                height: bounds.height,
                opacity: 0,
              }}
              animate={{
                top: bounds.top,
                left: bounds.left,
                width: bounds.width,
                height: bounds.height,
                opacity: 1,
              }}
              exit={{
                opacity: 0,
                transition: { duration: 0.12, ease: "easeOut" },
              }}
              transition={transition}
              style={{
                position: "absolute",
                zIndex: 0,
                pointerEvents: "none",
                ...style,
              }}
              className={cn(
                "rounded-xl pointer-events-none",
                className,
                activeClassName
              )}
            />
          )}
        </AnimatePresence>
        {children}
      </div>
    </HighlightContext.Provider>
  )
}

export function HighlightItem({
  children,
  id,
  activeClassName = "bg-white/[0.12] border border-white/10 shadow-xs",
  disabled = false,
  className,
  ...props
}) {
  const highlight = useHighlight()
  const localRef = React.useRef(null)
  const autoId = React.useId()
  const itemId = id || autoId

  if (!highlight || !highlight.enabled || disabled) {
    return children
  }

  const handleMouseEnter = (e) => {
    if (highlight.hover && localRef.current) {
      const rect = localRef.current.getBoundingClientRect()
      highlight.setBounds(rect, activeClassName, itemId)
    }
    children.props?.onMouseEnter?.(e)
  }

  const handleMouseLeave = (e) => {
    if (highlight.hover) {
      highlight.clearBounds()
    }
    children.props?.onMouseLeave?.(e)
  }

  const handleFocus = (e) => {
    if (localRef.current) {
      const rect = localRef.current.getBoundingClientRect()
      highlight.setBounds(rect, activeClassName, itemId)
    }
    children.props?.onFocus?.(e)
  }

  const handleBlur = (e) => {
    highlight.clearBounds()
    children.props?.onBlur?.(e)
  }

  return React.cloneElement(children, {
    ref: (node) => {
      localRef.current = node
      const { ref: childRef } = children
      if (typeof childRef === "function") {
        childRef(node)
      } else if (childRef && typeof childRef === "object") {
        childRef.current = node
      }
    },
    "data-highlight": "true",
    "data-highlight-id": itemId,
    onMouseEnter: handleMouseEnter,
    onMouseLeave: handleMouseLeave,
    onFocus: handleFocus,
    onBlur: handleBlur,
    ...props,
  })
}
