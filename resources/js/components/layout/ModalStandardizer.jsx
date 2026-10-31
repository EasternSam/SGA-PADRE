import { useEffect } from 'react';

/**
 * ModalStandardizer
 * 
 * Estandariza globalmente el comportamiento de TODOS los modales del sistema (SGA):
 * 1. Cierre automático al hacer clic fuera del modal (en el backdrop).
 * 2. Cierre automático al presionar la tecla Escape (ESC).
 * 3. Bloqueo de desplazamiento (scroll lock) del fondo (body) mientras haya modales activos.
 * 4. Prevención de cierres accidentales al arrastrar selecciones de texto dentro del modal.
 * 5. Compatibilidad universal con modales existentes y futuros en toda la plataforma.
 */
export default function ModalStandardizer() {
    useEffect(() => {
        let mouseDownTarget = null;
        let isInsideModalOnDown = false;

        const handleMouseDown = (e) => {
            mouseDownTarget = e.target;
            const modalContent = e.target.closest(
                '[class*="max-w-"], [class*="rounded-2xl"], [class*="rounded-3xl"], [data-modal-content]'
            );
            isInsideModalOnDown = !!modalContent;
        };

        const handleMouseUp = (e) => {
            const startTarget = mouseDownTarget;
            mouseDownTarget = null;
            if (!startTarget) return;

            // Si el mouse se presionó dentro del modal, no cerrar aunque el cursor se suelte fuera
            if (isInsideModalOnDown) {
                isInsideModalOnDown = false;
                return;
            }

            // Identificar el backdrop del modal
            const backdrop = startTarget.closest(
                'div[class*="fixed inset-0"][class*="z-50"]:not([class*="pointer-events-none"])'
            );
            if (!backdrop) return;

            // Verificar si el elemento soltado o clickeado es o está dentro del contenido del modal
            const modalBox = backdrop.querySelector(
                '[class*="max-w-"], [class*="rounded-2xl"], [class*="rounded-3xl"], [data-modal-content]'
            );

            if (modalBox && (modalBox.contains(startTarget) || modalBox.contains(e.target))) {
                return;
            }

            // Ignorar elementos de soporte contextual (menús desplegables Radix, selectores, toasts, pickers)
            if (
                startTarget.closest(
                    '[role="listbox"], [role="menu"], [data-radix-popper-content-wrapper], #toast-container, .flatpickr-calendar, .select__menu'
                )
            ) {
                return;
            }

            // Buscar y ejecutar el botón de cierre del modal
            const closeBtn = findCloseButton(modalBox || backdrop);
            if (closeBtn) {
                closeBtn.click();
            }
        };

        const handleKeyDown = (e) => {
            if (e.key !== 'Escape') return;

            // Seleccionar los modales visibles
            const openModals = Array.from(
                document.querySelectorAll(
                    'div[class*="fixed inset-0"][class*="z-50"]:not([class*="pointer-events-none"])'
                )
            ).filter((el) => {
                const style = window.getComputedStyle(el);
                return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
            });

            if (openModals.length === 0) return;

            // Tomar el modal superior
            const topModal = openModals[openModals.length - 1];
            const modalBox = topModal.querySelector(
                '[class*="max-w-"], [class*="rounded-2xl"], [class*="rounded-3xl"], [data-modal-content]'
            );

            const closeBtn = findCloseButton(modalBox || topModal);
            if (closeBtn) {
                e.preventDefault();
                e.stopPropagation();
                closeBtn.click();
            }
        };

        function findCloseButton(container) {
            if (!container) return null;

            // 1. Selector explícito data-close o aria-label
            const explicit = container.querySelector(
                '[data-close-modal], button[aria-label*="cerrar" i], button[aria-label*="close" i]'
            );
            if (explicit) return explicit;

            // 2. Botón en la cabecera del modal
            const header = container.querySelector(
                '[class*="border-b"], header, div:has(> h2), div:has(> h3), div:has(> h4)'
            );
            if (header) {
                const headerBtns = header.querySelectorAll('button');
                for (const btn of headerBtns) {
                    const text = btn.textContent.trim();
                    if (
                        text === '×' ||
                        text.toLowerCase() === 'cerrar' ||
                        btn.querySelector('svg, .lucide-x')
                    ) {
                        return btn;
                    }
                }
            }

            // 3. Botones Cancelar / Cerrar / Entendido
            const allButtons = container.querySelectorAll('button');
            for (const btn of allButtons) {
                const text = btn.textContent?.trim().toLowerCase();
                if (text === 'cancelar' || text === 'cerrar' || text === 'entendido') {
                    return btn;
                }
            }

            // 4. Cualquier botón que tenga un icono de X
            for (const btn of allButtons) {
                const svg = btn.querySelector('svg');
                if (svg) {
                    const html = svg.innerHTML;
                    if (
                        html.includes('18L18 6') ||
                        html.includes('M6 18L18 6') ||
                        svg.classList.contains('lucide-x') ||
                        btn.className.includes('close')
                    ) {
                        return btn;
                    }
                }
                if (btn.textContent.trim() === '×') {
                    return btn;
                }
            }

            // 5. Si la cabecera tiene algún botón, usar el último de la cabecera
            if (header) {
                const lastHeaderBtn = header.querySelector('button');
                if (lastHeaderBtn) return lastHeaderBtn;
            }

            return null;
        }

        // Gestión del scroll del body
        const updateScrollLock = () => {
            const hasModal = document.querySelector(
                'div[class*="fixed inset-0"][class*="z-50"]:not([class*="pointer-events-none"])'
            );
            if (hasModal) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        };

        const observer = new MutationObserver(() => {
            updateScrollLock();
        });

        observer.observe(document.body, { childList: true, subtree: true });
        updateScrollLock();

        window.addEventListener('mousedown', handleMouseDown, true);
        window.addEventListener('mouseup', handleMouseUp, true);
        window.addEventListener('keydown', handleKeyDown, true);

        return () => {
            observer.disconnect();
            window.removeEventListener('mousedown', handleMouseDown, true);
            window.removeEventListener('mouseup', handleMouseUp, true);
            window.removeEventListener('keydown', handleKeyDown, true);
            document.body.style.overflow = '';
        };
    }, []);

    return null;
}
