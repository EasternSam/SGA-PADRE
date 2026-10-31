import React, { useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { X } from 'lucide-react';

/**
 * Modal Component Estandarizado (SGA)
 * 
 * Componente canónico para modales en toda la plataforma:
 * - Cierre garantizado al hacer clic fuera del modal.
 * - Cierre garantizado con tecla Escape.
 * - Ajuste dinámico a cualquier altura de pantalla (max-h responsivo).
 * - Scroll interior suave con overscroll-contain.
 * - Renderizado en Portal de nivel raíz.
 */
export default function Modal({
    isOpen = false,
    onClose,
    title,
    subtitle,
    children,
    footer,
    maxWidth = 'max-w-2xl',
    showCloseButton = true,
    closeOnEscape = true,
    closeOnClickOutside = true,
    className = '',
    contentClassName = '',
}) {
    const dialogRef = useRef(null);

    useEffect(() => {
        if (!isOpen) return;

        const handleKeyDown = (e) => {
            if (e.key === 'Escape' && closeOnEscape) {
                e.stopPropagation();
                onClose?.();
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, closeOnEscape, onClose]);

    if (!isOpen) return null;

    const handleBackdropClick = (e) => {
        if (e.target === e.currentTarget && closeOnClickOutside) {
            onClose?.();
        }
    };

    const modalContent = (
        <div
            className={`fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-sm animate-in fade-in duration-200 overflow-y-auto ${className}`}
            onClick={handleBackdropClick}
            data-modal="true"
        >
            <div
                ref={dialogRef}
                onClick={(e) => e.stopPropagation()}
                className={`relative w-full ${maxWidth} bg-white dark:bg-[#161622] rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] max-h-[calc(100dvh-2rem)] sm:max-h-[calc(100dvh-3rem)] overflow-hidden animate-in zoom-in-95 duration-200 my-auto ${contentClassName}`}
                data-modal-content="true"
            >
                {/* Header */}
                {(title || showCloseButton) && (
                    <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                        <div>
                            {title && (
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white tracking-tight">
                                    {title}
                                </h3>
                            )}
                            {subtitle && (
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {subtitle}
                                </p>
                            )}
                        </div>
                        {showCloseButton && (
                            <button
                                type="button"
                                onClick={onClose}
                                data-close-modal="true"
                                className="rounded-xl p-2 text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                aria-label="Cerrar modal"
                            >
                                <X className="size-4.5" />
                            </button>
                        )}
                    </div>
                )}

                {/* Body con scroll independiente */}
                <div className="px-6 py-5 overflow-y-auto flex-1 overscroll-contain">
                    {children}
                </div>

                {/* Footer opcional */}
                {footer && (
                    <div className="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-white/[0.02] shrink-0">
                        {footer}
                    </div>
                )}
            </div>
        </div>
    );

    return typeof document !== 'undefined' ? createPortal(modalContent, document.body) : modalContent;
}
