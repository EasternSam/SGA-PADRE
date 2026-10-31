import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva } from "class-variance-authority"
import { PanelLeft } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"
import { useIsMobile } from "@/lib/use-mobile"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/react"
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip"
import { Highlight, HighlightItem } from "@/components/ui/highlight"

const SIDEBAR_COOKIE_NAME = "sidebar_state"
const SIDEBAR_COOKIE_MAX_AGE = 60 * 60 * 24 * 7
const SIDEBAR_WIDTH = "16rem"
const SIDEBAR_WIDTH_MOBILE = "18rem"
const SIDEBAR_WIDTH_ICON = "4.25rem"
const SIDEBAR_KEYBOARD_SHORTCUT = "b"

const SidebarContext = React.createContext(null)

function useSidebar() {
  const context = React.useContext(SidebarContext)
  if (!context) {
    throw new Error("useSidebar must be used within a SidebarProvider.")
  }
  return context
}

const SidebarProvider = React.forwardRef(
  (
    {
      defaultOpen = true,
      open: openProp,
      onOpenChange: setOpenProp,
      className,
      style,
      children,
      ...props
    },
    ref
  ) => {
    const isMobile = useIsMobile()
    const [openMobile, setOpenMobile] = React.useState(false)

    // Leer estado inicial persistido en localStorage o cookie
    const [_open, _setOpen] = React.useState(() => {
      if (typeof window !== "undefined") {
        const saved = localStorage.getItem(SIDEBAR_COOKIE_NAME)
        if (saved !== null) {
          return saved === "true"
        }
      }
      return defaultOpen
    })

    const open = openProp !== undefined ? openProp : _open
    const setOpen = React.useCallback(
      (value) => {
        const openState = typeof value === "function" ? value(open) : value
        if (setOpenProp) {
          setOpenProp(openState)
        } else {
          _setOpen(openState)
        }
        if (typeof window !== "undefined") {
          localStorage.setItem(SIDEBAR_COOKIE_NAME, String(openState))
          document.cookie = `${SIDEBAR_COOKIE_NAME}=${openState}; path=/; max-age=${SIDEBAR_COOKIE_MAX_AGE}`
        }
      },
      [setOpenProp, open]
    )

    // Alternar sidebar
    const toggleSidebar = React.useCallback(() => {
      return isMobile
        ? setOpenMobile((prev) => !prev)
        : setOpen((prev) => !prev)
    }, [isMobile, setOpen, setOpenMobile])

    // Atajo de teclado: Ctrl+B / Cmd+B
    React.useEffect(() => {
      const handleKeyDown = (event) => {
        if (
          event.key === SIDEBAR_KEYBOARD_SHORTCUT &&
          (event.metaKey || event.ctrlKey)
        ) {
          event.preventDefault()
          toggleSidebar()
        }
      }

      window.addEventListener("keydown", handleKeyDown)
      return () => window.removeEventListener("keydown", handleKeyDown)
    }, [toggleSidebar])

    const state = open ? "expanded" : "collapsed"

    const contextValue = React.useMemo(
      () => ({
        state,
        open,
        setOpen,
        isMobile,
        openMobile,
        setOpenMobile,
        toggleSidebar,
      }),
      [state, open, setOpen, isMobile, openMobile, setOpenMobile, toggleSidebar]
    )

    return (
      <SidebarContext.Provider value={contextValue}>
        <TooltipProvider delayDuration={0}>
          <div
            style={{
              "--sidebar-width": SIDEBAR_WIDTH,
              "--sidebar-width-icon": SIDEBAR_WIDTH_ICON,
              ...style,
            }}
            className={cn(
              "group/sidebar-wrapper flex min-h-svh w-full text-slate-800 dark:text-slate-100",
              className
            )}
            ref={ref}
            {...props}
          >
            {children}
          </div>
        </TooltipProvider>
      </SidebarContext.Provider>
    )
  }
)
SidebarProvider.displayName = "SidebarProvider"

const Sidebar = React.forwardRef(
  (
    {
      side = "left",
      variant = "sidebar",
      collapsible = "icon",
      className,
      style,
      children,
      animateOnHover = true,
      containerClassName,
      transition = { type: "spring", stiffness: 380, damping: 30 },
      ...props
    },
    ref
  ) => {
    const { isMobile, state, openMobile, setOpenMobile } = useSidebar()

    if (collapsible === "none") {
      return (
        <Highlight
          enabled={animateOnHover}
          hover
          mode="parent"
          transition={transition}
          containerClassName={containerClassName}
        >
          <div
            className={cn(
              "flex h-full w-[var(--sidebar-width,16rem)] flex-col text-sidebar-foreground",
              className
            )}
            style={style}
            ref={ref}
            {...props}
          >
            {children}
          </div>
        </Highlight>
      )
    }

    // Móvil: Drawer deslizable con Backdrop blur
    if (isMobile) {
      return (
        <>
          <AnimatePresence>
            {openMobile && (
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                onClick={() => setOpenMobile(false)}
                className="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm"
              />
            )}
          </AnimatePresence>
          <div
            ref={ref}
            className={cn(
              "fixed inset-y-0 left-0 z-50 flex h-full w-[var(--sidebar-width-mobile,18rem)] flex-col shadow-2xl transition-transform duration-400 ease-[cubic-bezier(0.75,0,0.25,1)]",
              openMobile ? "translate-x-0" : "-translate-x-full",
              className
            )}
            style={style}
            data-mobile="true"
            {...props}
          >
            <Highlight
              enabled={animateOnHover}
              hover
              mode="parent"
              transition={transition}
              containerClassName="h-full w-full"
            >
              <div
                data-sidebar="sidebar"
                data-slot="sidebar-inner"
                className="flex h-full w-full flex-col overflow-hidden bg-transparent"
              >
                {children}
              </div>
            </Highlight>
          </div>
        </>
      )
    }

    // Escritorio: Contenedor con curvas cubic-bezier de Animate UI
    return (
      <div
        ref={ref}
        className="group peer hidden md:block text-sidebar-foreground select-none"
        data-state={state}
        data-collapsible={state === "collapsed" ? collapsible : ""}
        data-variant={variant}
        data-side={side}
        data-slot="sidebar"
      >
        {/* Espaciador fluido en el flujo del documento con bezier con rebote de Animate UI */}
        <div
          data-slot="sidebar-gap"
          className={cn(
            "relative h-svh bg-transparent transition-[width] duration-400 ease-[cubic-bezier(0.7,-0.15,0.25,1.15)]",
            state === "collapsed" ? "w-[var(--sidebar-width-icon,4.25rem)]" : "w-[var(--sidebar-width,16rem)]"
          )}
        />
        {/* Sidebar flotante/fijo con bezier de Animate UI */}
        <div
          data-slot="sidebar-container"
          className={cn(
            "fixed inset-y-0 z-30 hidden h-svh transition-[left,right,width] duration-400 ease-[cubic-bezier(0.75,0,0.25,1)] md:flex flex-col border-r border-white/10 shadow-xl overflow-hidden",
            side === "left" ? "left-0" : "right-0",
            state === "collapsed" ? "w-[var(--sidebar-width-icon,4.25rem)]" : "w-[var(--sidebar-width,16rem)]",
            className
          )}
          style={style}
          {...props}
        >
          <Highlight
            key={state}
            containerClassName="h-full w-full overflow-hidden"
            enabled={animateOnHover}
            hover
            mode="parent"
            transition={transition}
          >
            <div
              data-sidebar="sidebar"
              data-slot="sidebar-inner"
              className="flex h-full w-full flex-col bg-transparent overflow-hidden"
            >
              {children}
            </div>
          </Highlight>
        </div>
      </div>
    )
  }
)
Sidebar.displayName = "Sidebar"

const SidebarTrigger = React.forwardRef(
  ({ className, onClick, ...props }, ref) => {
    const { toggleSidebar } = useSidebar()

    return (
      <Button
        ref={ref}
        data-sidebar="trigger"
        variant="ghost"
        size="icon"
        className={cn(
          "h-8 w-8 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-white/[0.08] text-gray-600 dark:text-gray-300",
          className
        )}
        onClick={(event) => {
          onClick?.(event)
          toggleSidebar()
        }}
        aria-label="Alternar barra lateral"
        title="Alternar barra lateral (Ctrl+B)"
        {...props}
      >
        <PanelLeft className="h-4 w-4" />
        <span className="sr-only">Toggle Sidebar</span>
      </Button>
    )
  }
)
SidebarTrigger.displayName = "SidebarTrigger"

const SidebarRail = React.forwardRef(({ className, ...props }, ref) => {
  const { toggleSidebar } = useSidebar()

  return (
    <button
      ref={ref}
      data-sidebar="rail"
      aria-label="Toggle Sidebar"
      tabIndex={-1}
      onClick={toggleSidebar}
      title="Alternar barra lateral (Ctrl+B)"
      className={cn(
        "absolute inset-y-0 z-20 hidden w-3 -translate-x-1/2 transition-all ease-linear after:absolute after:inset-y-0 after:left-1/2 after:w-[2px] after:transition-colors hover:after:bg-white/40 right-[-6px] md:flex cursor-w-resize group-data-[collapsible=icon]:cursor-e-resize",
        className
      )}
      {...props}
    />
  )
})
SidebarRail.displayName = "SidebarRail"

const SidebarInset = React.forwardRef(({ className, ...props }, ref) => {
  return (
    <main
      ref={ref}
      data-slot="sidebar-inset"
      className={cn(
        "relative flex min-h-svh flex-1 flex-col bg-background min-w-0 transition-all duration-300 ease-in-out",
        className
      )}
      {...props}
    />
  )
})
SidebarInset.displayName = "SidebarInset"

const SidebarHeader = React.forwardRef(({ className, style, ...props }, ref) => {
  return (
    <div
      ref={ref}
      data-slot="sidebar-header"
      data-sidebar="header"
      className={cn(
        "flex flex-col gap-2 p-3.5 border-b border-white/10 shrink-0 bg-transparent relative z-10",
        className
      )}
      style={{ background: "transparent", backgroundColor: "transparent", ...style }}
      {...props}
    />
  )
})
SidebarHeader.displayName = "SidebarHeader"

const SidebarFooter = React.forwardRef(({ className, style, ...props }, ref) => {
  return (
    <div
      ref={ref}
      data-slot="sidebar-footer"
      data-sidebar="footer"
      className={cn(
        "flex flex-col gap-2 p-3 border-t border-white/10 shrink-0 mt-auto bg-transparent relative z-10",
        className
      )}
      style={{ background: "transparent", backgroundColor: "transparent", ...style }}
      {...props}
    />
  )
})
SidebarFooter.displayName = "SidebarFooter"

const SidebarSeparator = React.forwardRef(({ className, ...props }, ref) => {
  return (
    <div
      ref={ref}
      data-slot="sidebar-separator"
      data-sidebar="separator"
      className={cn("mx-2 h-px bg-white/10 my-1.5", className)}
      {...props}
    />
  )
})
SidebarSeparator.displayName = "SidebarSeparator"

const SidebarContent = React.forwardRef(({ className, style, ...props }, ref) => {
  return (
    <div
      ref={ref}
      data-slot="sidebar-content"
      data-sidebar="content"
      className={cn(
        "flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto overflow-x-hidden p-2.5 bg-transparent sidebar-scroll relative z-10",
        className
      )}
      style={{ background: "transparent", backgroundColor: "transparent", border: "none", ...style }}
      {...props}
    />
  )
})
SidebarContent.displayName = "SidebarContent"

const SidebarGroup = React.forwardRef(({ className, ...props }, ref) => {
  return (
    <div
      ref={ref}
      data-slot="sidebar-group"
      data-sidebar="group"
      className={cn("relative flex w-full min-w-0 flex-col py-1.5", className)}
      {...props}
    />
  )
})
SidebarGroup.displayName = "SidebarGroup"

const SidebarGroupLabel = React.forwardRef(
  ({ className, asChild = false, ...props }, ref) => {
    const { state } = useSidebar()
    const Comp = asChild ? Slot : "div"

    return (
      <Comp
        ref={ref}
        data-slot="sidebar-group-label"
        data-sidebar="group-label"
        className={cn(
          "flex h-6 shrink-0 items-center rounded-md px-2.5 text-[10px] font-bold uppercase tracking-wider text-white/40 transition-[margin,opacity,height] duration-200 ease-linear select-none",
          state === "collapsed" && "opacity-0 h-0 my-0 overflow-hidden pointer-events-none",
          className
        )}
        {...props}
      />
    )
  }
)
SidebarGroupLabel.displayName = "SidebarGroupLabel"

const SidebarGroupAction = React.forwardRef(
  ({ className, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button"

    return (
      <Comp
        ref={ref}
        data-slot="sidebar-group-action"
        data-sidebar="group-action"
        className={cn(
          "absolute right-2 top-2 flex aspect-square w-5 items-center justify-center rounded-md p-0 text-white/60 outline-none ring-sidebar-ring transition-transform hover:bg-white/10 hover:text-white focus-visible:ring-2",
          className
        )}
        {...props}
      />
    )
  }
)
SidebarGroupAction.displayName = "SidebarGroupAction"

const SidebarGroupContent = React.forwardRef(({ className, ...props }, ref) => {
  return (
    <div
      ref={ref}
      data-slot="sidebar-group-content"
      data-sidebar="group-content"
      className={cn("w-full text-sm", className)}
      {...props}
    />
  )
})
SidebarGroupContent.displayName = "SidebarGroupContent"

const SidebarMenu = React.forwardRef(({ className, ...props }, ref) => (
  <ul
    ref={ref}
    data-slot="sidebar-menu"
    data-sidebar="menu"
    className={cn("flex w-full min-w-0 flex-col gap-1 list-none p-0 m-0", className)}
    {...props}
  />
))
SidebarMenu.displayName = "SidebarMenu"

const SidebarMenuItem = React.forwardRef(({ className, ...props }, ref) => (
  <li
    ref={ref}
    data-slot="sidebar-menu-item"
    data-sidebar="menu-item"
    className={cn("group/menu-item relative list-none", className)}
    {...props}
  />
))
SidebarMenuItem.displayName = "SidebarMenuItem"

const sidebarMenuButtonVariants = cva(
  "peer/menu-button flex w-full items-center gap-2.5 rounded-xl text-[13px] font-medium transition-all duration-200 outline-none select-none active:scale-[0.98]",
  {
    variants: {
      variant: {
        default:
          "text-white/70 hover:text-white [&:not([data-highlight])]:hover:bg-white/[0.09]",
        active:
          "bg-white/20 text-white font-semibold shadow-[0_2px_8px_rgba(0,0,0,0.12)] border border-white/20",
      },
      size: {
        default: "h-9 px-3 py-2",
        sm: "h-8 text-xs px-2.5",
        lg: "h-11 px-3.5",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

const SidebarMenuButton = React.forwardRef(
  (
    {
      asChild = false,
      isActive = false,
      variant = "default",
      size = "default",
      tooltip,
      className,
      children,
      ...props
    },
    ref
  ) => {
    const { isMobile, state } = useSidebar()
    const Comp = asChild ? Slot : (props.href ? Link : "button")

    const isCollapsed = state === "collapsed" && !isMobile
    const computedVariant = isActive ? "active" : variant

    const buttonElement = (
      <Comp
        ref={ref}
        data-slot="sidebar-menu-button"
        data-sidebar="menu-button"
        data-size={size}
        data-active={isActive}
        prefetch={props.href ? "hover" : undefined}
        className={cn(
          sidebarMenuButtonVariants({ variant: computedVariant, size }),
          isCollapsed && "justify-center px-0 h-10 w-10 mx-auto [&>span]:hidden [&>svg:not(:first-child)]:hidden",
          className
        )}
        {...props}
      >
        {children}
      </Comp>
    )

    // HighlightItem wrapping for spring floating pill effect
    const highlightedButton = (
      <HighlightItem activeClassName="bg-white/[0.12] border border-white/10 rounded-xl shadow-xs">
        {buttonElement}
      </HighlightItem>
    )

    // En modo colapsado, envolver con Tooltip flotante con Radix
    if (tooltip && isCollapsed) {
      return (
        <Tooltip>
          <TooltipTrigger asChild>{highlightedButton}</TooltipTrigger>
          <TooltipContent side="right" align="center" className="font-medium text-xs">
            {tooltip}
          </TooltipContent>
        </Tooltip>
      )
    }

    return highlightedButton
  }
)
SidebarMenuButton.displayName = "SidebarMenuButton"

const SidebarMenuAction = React.forwardRef(
  ({ className, asChild = false, showOnHover = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button"

    return (
      <Comp
        ref={ref}
        data-slot="sidebar-menu-action"
        data-sidebar="menu-action"
        className={cn(
          "absolute right-1 top-1.5 flex aspect-square w-5 items-center justify-center rounded-md p-0 text-white/60 outline-none transition-transform hover:bg-white/10 hover:text-white focus-visible:ring-2",
          showOnHover && "opacity-0 group-hover/menu-item:opacity-100",
          className
        )}
        {...props}
      />
    )
  }
)
SidebarMenuAction.displayName = "SidebarMenuAction"

const SidebarMenuBadge = React.forwardRef(({ className, ...props }, ref) => {
  const { state, isMobile } = useSidebar()
  if (state === "collapsed" && !isMobile) return null

  return (
    <div
      ref={ref}
      data-slot="sidebar-menu-badge"
      data-sidebar="menu-badge"
      className={cn(
        "ml-auto flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-[10px] font-bold text-white/90 bg-white/20 pointer-events-none select-none",
        className
      )}
      {...props}
    />
  )
})
SidebarMenuBadge.displayName = "SidebarMenuBadge"

const SidebarMenuSkeleton = React.forwardRef(
  ({ className, showIcon = false, ...props }, ref) => {
    return (
      <div
        ref={ref}
        data-slot="sidebar-menu-skeleton"
        data-sidebar="menu-skeleton"
        className={cn("rounded-xl h-8 flex gap-2 px-2 items-center animate-pulse bg-white/10", className)}
        {...props}
      >
        {showIcon && (
          <div className="h-4 w-4 rounded-md bg-white/20" />
        )}
        <div className="h-3 flex-1 rounded bg-white/15" />
      </div>
    )
  }
)
SidebarMenuSkeleton.displayName = "SidebarMenuSkeleton"

const SidebarMenuSub = React.forwardRef(({ className, ...props }, ref) => {
  const { state, isMobile } = useSidebar()
  if (state === "collapsed" && !isMobile) return null

  return (
    <ul
      ref={ref}
      data-slot="sidebar-menu-sub"
      data-sidebar="menu-sub"
      className={cn(
        "ml-4 pl-3 border-l border-white/15 flex flex-col gap-0.5 mt-1 list-none",
        className
      )}
      {...props}
    />
  )
})
SidebarMenuSub.displayName = "SidebarMenuSub"

const SidebarMenuSubItem = React.forwardRef(({ className, ...props }, ref) => (
  <li
    ref={ref}
    data-slot="sidebar-menu-sub-item"
    data-sidebar="menu-sub-item"
    className={cn("list-none group/menu-sub-item relative", className)}
    {...props}
  />
))
SidebarMenuSubItem.displayName = "SidebarMenuSubItem"

const SidebarMenuSubButton = React.forwardRef(
  ({ asChild = false, size = "md", isActive, className, ...props }, ref) => {
    const Comp = asChild ? Slot : (props.href ? Link : "button")

    return (
      <HighlightItem activeClassName="bg-white/[0.10] border border-white/10 rounded-lg">
        <Comp
          ref={ref}
          data-slot="sidebar-menu-sub-button"
          data-sidebar="menu-sub-button"
          data-size={size}
          data-active={isActive}
          prefetch={props.href ? "hover" : undefined}
          className={cn(
            "block px-2.5 py-1.5 text-xs rounded-lg transition-colors select-none",
            isActive
              ? "bg-white/20 text-white font-semibold border border-white/20"
              : "text-white/70 hover:text-white [&:not([data-highlight])]:hover:bg-white/10",
            className
          )}
          {...props}
        />
      </HighlightItem>
    )
  }
)
SidebarMenuSubButton.displayName = "SidebarMenuSubButton"

// Submenú colapsable animado con Framer Motion
function CollapsibleSidebarMenu({ open, children, className }) {
  return (
    <AnimatePresence initial={false}>
      {open && (
        <motion.div
          initial={{ height: 0, opacity: 0 }}
          animate={{ height: "auto", opacity: 1 }}
          exit={{ height: 0, opacity: 0 }}
          transition={{ duration: 0.28, ease: [0.16, 1, 0.3, 1] }}
          className={cn("overflow-hidden", className)}
        >
          {children}
        </motion.div>
      )}
    </AnimatePresence>
  )
}

export {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupAction,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuAction,
  SidebarMenuBadge,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSkeleton,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  SidebarProvider,
  SidebarRail,
  SidebarSeparator,
  SidebarTrigger,
  CollapsibleSidebarMenu,
  Highlight,
  HighlightItem,
  useSidebar,
}
