import React, { useState, useEffect, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

/* Deterministic High-Contrast SVG QR Code Generator for Kiosk Payment */
function KioskQrCodeSvg({ value = 'ACADEMIC-KIOSK-QR' }) {
    const size = 21;
    const cells = useMemo(() => {
        let hash = 2166136261;
        for (let i = 0; i < value.length; i++) {
            hash ^= value.charCodeAt(i);
            hash = Math.imul(hash, 16777619);
        }
        const grid = Array.from({ length: size }, () => Array(size).fill(false));

        const drawFinder = (r0, c0) => {
            for (let r = 0; r < 7; r++) {
                for (let c = 0; c < 7; c++) {
                    const isBorder = r === 0 || r === 6 || c === 0 || c === 6;
                    const isInner = r >= 2 && r <= 4 && c >= 2 && c <= 4;
                    grid[r0 + r][c0 + c] = isBorder || isInner;
                }
            }
        };

        drawFinder(0, 0);
        drawFinder(0, size - 7);
        drawFinder(size - 7, 0);

        let seed = Math.abs(hash) || 1234567;
        for (let r = 0; r < size; r++) {
            for (let c = 0; c < size; c++) {
                const inTopLeft = r < 8 && c < 8;
                const inTopRight = r < 8 && c >= size - 8;
                const inBottomLeft = r >= size - 8 && c < 8;
                if (inTopLeft || inTopRight || inBottomLeft) continue;
                seed = (seed * 1664525 + 1013904223) % 4294967296;
                grid[r][c] = (seed & 3) !== 0;
            }
        }
        return grid;
    }, [value]);

    return (
        <svg viewBox={`0 0 ${size} ${size}`} className="w-48 h-48 mx-auto" shapeRendering="crispEdges">
            {cells.map((row, rIdx) =>
                row.map((filled, cIdx) =>
                    filled ? <rect key={`${rIdx}-${cIdx}`} x={cIdx} y={rIdx} width={1} height={1} fill="#111827" /> : null
                )
            )}
        </svg>
    );
}

export default function Finances({ student, pendingPayments = [], paidPayments = [], totalPending = 0 }) {
    const [selectedIds, setSelectedIds] = useState(() => pendingPayments.map((p) => p.id));
    const [paymentMethod, setPaymentMethod] = useState('qr'); // 'cashier' | 'card' | 'qr'
    const [activeView, setActiveView] = useState('cart'); // 'cart' | 'checkout'
    const [secondsLeft, setSecondsLeft] = useState(14 * 60 + 59); // 14:59 as in Image 2
    const [showInstructions, setShowInstructions] = useState(true);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setSelectedIds(pendingPayments.map((p) => p.id));
    }, [pendingPayments]);

    useEffect(() => {
        if (activeView !== 'checkout') return;
        setSecondsLeft(14 * 60 + 59);
        const timer = setInterval(() => {
            setSecondsLeft((prev) => (prev > 0 ? prev - 1 : 0));
        }, 1000);
        return () => clearInterval(timer);
    }, [activeView]);

    const toggleSelect = (id) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
    };

    const selectedItems = useMemo(
        () => pendingPayments.filter((p) => selectedIds.includes(p.id)),
        [pendingPayments, selectedIds]
    );

    const summary = useMemo(() => {
        const subtotal = selectedItems.reduce((acc, p) => acc + Number(p.base_amount ?? p.amount ?? 0), 0);
        const discount = selectedItems.reduce((acc, p) => acc + Number(p.discount ?? 0), 0);
        const surcharge = selectedItems.reduce((acc, p) => acc + Number(p.surcharge ?? 0), 0);
        const total = selectedItems.reduce((acc, p) => acc + Number(p.amount ?? 0), 0);
        return { subtotal, discount, surcharge, total };
    }, [selectedItems]);

    const mins = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
    const secs = String(secondsLeft % 60).padStart(2, '0');

    const handleConfirmPayment = () => {
        if (selectedIds.length === 0) return;
        setProcessing(true);
        router.post(
            '/kiosk/finances/pay',
            {
                payment_ids: selectedIds,
                payment_method: paymentMethod,
            },
            {
                onFinish: () => {
                    setProcessing(false);
                    setActiveView('cart');
                },
            }
        );
    };

    return (
        <KioskLayout
            title={activeView === 'checkout' ? 'Pago QR / Terminal' : 'Orden y Estado de Cuenta'}
            student={student}
            backUrl="/kiosk/dashboard"
            showBack={true}
        >
            <Head title="Autopago y Estado de Cuenta - Kiosco" />

            <div className="max-w-5xl mx-auto w-full py-2">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
                    {/* Left Column: Cart & Purchase Details (Image 2 left screen "Order") */}
                    <div className="lg:col-span-7 space-y-5">
                        {/* Our Cart Card */}
                        <div className="rounded-3xl bg-[#F8FAFC] dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 p-6 shadow-xs">
                            <div className="flex items-center justify-between mb-4">
                                <div>
                                    <h2 className="text-lg font-bold text-[#111827] dark:text-white">
                                        Mis Cargos Pendientes
                                    </h2>
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        Selecciona las cuotas o servicios que deseas pagar hoy
                                    </p>
                                </div>
                                {pendingPayments.length > 0 && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setSelectedIds(
                                                selectedIds.length === pendingPayments.length
                                                    ? []
                                                    : pendingPayments.map((p) => p.id)
                                            )
                                        }
                                        className="text-xs font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400"
                                    >
                                        {selectedIds.length === pendingPayments.length
                                            ? 'Deseleccionar todos'
                                            : 'Seleccionar todos'}
                                    </button>
                                )}
                            </div>

                            {pendingPayments.length === 0 ? (
                                <div className="py-10 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <div className="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-400 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                                        ✓
                                    </div>
                                    <h3 className="text-lg font-bold text-[#111827] dark:text-white">
                                        ¡Estás al día con tus pagos!
                                    </h3>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        No tienes cuotas ni cargos vencidos en este momento.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {pendingPayments.map((item, idx) => {
                                        const isSelected = selectedIds.includes(item.id);
                                        const badgeColors = [
                                            'bg-orange-500 text-white',
                                            'bg-[#1F2937] text-white',
                                            'bg-indigo-600 text-white',
                                        ];
                                        return (
                                            <div
                                                key={item.id}
                                                onClick={() => toggleSelect(item.id)}
                                                className={`p-4 rounded-2xl bg-white dark:bg-slate-900 border-2 transition-all cursor-pointer flex items-center justify-between gap-4 ${
                                                    isSelected
                                                        ? 'border-[#1A5D57] shadow-xs'
                                                        : 'border-slate-100 dark:border-slate-800 opacity-75'
                                                }`}
                                            >
                                                <div className="flex items-center gap-3.5 min-w-0">
                                                    <div
                                                        className={`w-12 h-12 rounded-2xl flex items-center justify-center font-black text-base shrink-0 ${
                                                            badgeColors[idx % badgeColors.length]
                                                        }`}
                                                    >
                                                        {item.concept.charAt(0).toUpperCase()}
                                                    </div>
                                                    <div className="min-w-0">
                                                        <div className="font-bold text-sm md:text-base text-[#111827] dark:text-white truncate">
                                                            {item.concept}
                                                        </div>
                                                        <div className="text-xs font-bold text-[#1A5D57] dark:text-emerald-400 mt-0.5">
                                                            RD$ {Number(item.amount).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                        </div>
                                                        <div className="text-[11px] text-slate-400 mt-0.5">
                                                            Vence: {item.due_date}
                                                        </div>
                                                    </div>
                                                </div>

                                                {/* Quantity / Selection Pill (Image 2 right side of each cart item) */}
                                                <div className="flex items-center gap-2 shrink-0">
                                                    <span
                                                        className={`w-8 h-8 rounded-xl border-2 flex items-center justify-center font-bold text-sm transition-colors ${
                                                            isSelected
                                                                ? 'bg-[#1A5D57] border-[#1A5D57] text-white'
                                                                : 'bg-[#F3F4F6] dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-transparent'
                                                        }`}
                                                    >
                                                        ✓
                                                    </span>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}

                            {/* Recent Paid Receipts Strip */}
                            {paidPayments.length > 0 && (
                                <div className="mt-5 pt-4 border-t border-slate-200/70 dark:border-slate-700/70">
                                    <div className="flex items-center justify-between mb-2">
                                        <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                            Últimos Pagos Realizados
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => window.print()}
                                            className="text-xs font-bold text-[#1A5D57] dark:text-emerald-400 hover:underline"
                                        >
                                            Imprimir comprobante
                                        </button>
                                    </div>
                                    <div className="space-y-2">
                                        {paidPayments.slice(0, 2).map((paid) => (
                                            <div
                                                key={paid.id}
                                                className="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs"
                                            >
                                                <div>
                                                    <span className="font-bold text-[#111827] dark:text-white">{paid.concept}</span>
                                                    <span className="text-slate-400 ml-2">• NCF {paid.ncf}</span>
                                                </div>
                                                <span className="font-bold text-[#166534] dark:text-emerald-400">
                                                    RD$ {Number(paid.amount).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Purchase Details & Payment Method Selector (Image 2 bottom-left) */}
                        <div className="rounded-3xl bg-[#F8FAFC] dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 p-6 shadow-xs">
                            <h3 className="text-center text-sm font-bold text-[#111827] dark:text-white mb-4">
                                Detalles de Pago
                            </h3>

                            <div className="space-y-2.5 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="font-semibold text-slate-600 dark:text-slate-300">Subtotal</span>
                                    <span className="font-bold text-[#111827] dark:text-white">
                                        RD$ {summary.subtotal.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-slate-400">Recargos / Impuestos</span>
                                    <span className="text-slate-400">
                                        RD$ {summary.surcharge.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="font-semibold text-slate-600 dark:text-slate-300">Descuento / Beca</span>
                                    <span className="font-bold text-[#1A5D57] dark:text-emerald-400">
                                        -RD$ {summary.discount.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                                <div className="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-base">
                                    <span className="font-extrabold text-[#111827] dark:text-white">Total</span>
                                    <span className="font-black text-lg text-[#1A5D57] dark:text-emerald-400">
                                        RD$ {summary.total.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            </div>

                            {/* 3 Payment Method Tiles (Image 2: Pay at Cashier | Virtual Account/Card | QRIS) */}
                            <div className="mt-6">
                                <div className="text-center text-xs font-bold text-[#111827] dark:text-white mb-3">
                                    Método de Pago
                                </div>
                                <div className="grid grid-cols-3 gap-3">
                                    {[
                                        {
                                            id: 'cashier',
                                            label: 'Pagar en Caja',
                                            icon: (
                                                <svg className="w-5 h-5 mx-auto mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                                                    <rect x="3" y="6" width="18" height="14" rx="2" />
                                                    <path d="M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M12 11v4M9 13h6" strokeLinecap="round" />
                                                </svg>
                                            ),
                                        },
                                        {
                                            id: 'card',
                                            label: 'Tarjeta / NFC',
                                            icon: (
                                                <svg className="w-5 h-5 mx-auto mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                                    <line x1="2" y1="10" x2="22" y2="10" />
                                                </svg>
                                            ),
                                        },
                                        {
                                            id: 'qr',
                                            label: 'Código QR',
                                            icon: (
                                                <svg className="w-5 h-5 mx-auto mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                                    <path d="M14 14h3v3h-3zM18 18h3v3h-3zM18 14h3M14 18v3" strokeLinecap="round" />
                                                </svg>
                                            ),
                                        },
                                    ].map((m) => {
                                        const active = paymentMethod === m.id;
                                        return (
                                            <button
                                                key={m.id}
                                                type="button"
                                                onClick={() => {
                                                    setPaymentMethod(m.id);
                                                    setActiveView('checkout');
                                                }}
                                                className={`p-3.5 rounded-2xl border-2 text-center transition-all active:scale-95 ${
                                                    active
                                                        ? 'bg-[#E6F2F0] dark:bg-emerald-950/50 border-[#1A5D57] text-[#1A5D57] dark:text-emerald-300 font-bold'
                                                        : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-medium hover:border-slate-300'
                                                }`}
                                            >
                                                {m.icon}
                                                <div className="text-xs leading-tight">{m.label}</div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Primary Pill Button: Payment Process (Image 2 bottom-left) */}
                            <button
                                type="button"
                                disabled={selectedIds.length === 0 || processing}
                                onClick={() => setActiveView('checkout')}
                                className={`mt-5 w-full py-4 rounded-full font-bold text-sm transition-all active:scale-[0.98] ${
                                    selectedIds.length > 0 && !processing
                                        ? 'bg-[#1A5D57] hover:bg-[#134641] text-white shadow-md'
                                        : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'
                                }`}
                            >
                                {processing ? 'Procesando pago...' : 'Continuar al Pago'}
                            </button>
                        </div>
                    </div>

                    {/* Right Column: QRIS / Terminal Payment Card with Countdown Timer & Instructions (Image 2 right screen) */}
                    <div className="lg:col-span-5">
                        <div className="rounded-3xl bg-[#F8FAFC] dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 p-6 shadow-xs">
                            <div className="text-center mb-4">
                                <div className="text-sm font-bold text-[#111827] dark:text-white">
                                    Completa tu pago antes de que expire
                                </div>
                                <div className="text-xs text-slate-400 mt-0.5">
                                    Sesión protegida • Terminal de Autoservicio
                                </div>
                            </div>

                            {/* Dark Teal QR & Countdown Box (Exact match to Image 2 right screen) */}
                            <div className="rounded-3xl bg-[#1A5D57] p-6 text-white text-center shadow-md">
                                {/* 4-digit Countdown Timer Boxes */}
                                <div className="inline-flex items-center gap-1.5 mb-5">
                                    <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                        {mins[0]}
                                    </span>
                                    <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                        {mins[1]}
                                    </span>
                                    <span className="font-bold px-0.5">:</span>
                                    <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                        {secs[0]}
                                    </span>
                                    <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                        {secs[1]}
                                    </span>
                                </div>

                                {/* Center QR or Method Graphic */}
                                <div className="bg-white rounded-2xl p-4 max-w-[220px] mx-auto shadow-inner">
                                    {paymentMethod === 'qr' && (
                                        <KioskQrCodeSvg
                                            value={`KIOSK|${student?.student_code || 'EST'}|${summary.total}|${selectedIds.join(',')}`}
                                        />
                                    )}
                                    {paymentMethod === 'card' && (
                                        <div className="py-8 px-2 text-[#111827] flex flex-col items-center">
                                            <div className="w-16 h-16 rounded-2xl bg-[#E6F2F0] text-[#1A5D57] flex items-center justify-center mb-3 animate-pulse">
                                                <svg className="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                                    <line x1="2" y1="10" x2="22" y2="10" />
                                                </svg>
                                            </div>
                                            <div className="font-bold text-sm">Acerca o inserta tu Tarjeta</div>
                                            <div className="text-[11px] text-slate-500 mt-1">
                                                Lector sin contacto NFC / Chip activo
                                            </div>
                                        </div>
                                    )}
                                    {paymentMethod === 'cashier' && (
                                        <div className="py-8 px-2 text-[#111827] flex flex-col items-center">
                                            <div className="text-xs uppercase font-bold text-slate-400">Turno de Caja</div>
                                            <div className="text-4xl font-mono font-black text-[#1A5D57] my-2">
                                                C-{String((student?.id || 12) % 90 + 10)}
                                            </div>
                                            <div className="text-[11px] text-slate-500">
                                                Presenta este código en ventanilla
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Total Payment White Card (Image 2 right screen) */}
                            <div className="mt-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 p-4 text-center shadow-xs">
                                <div className="text-xs font-semibold text-slate-400">Total a Pagar</div>
                                <div className="text-2xl font-black text-[#1A5D57] dark:text-emerald-400 mt-0.5">
                                    RD$ {summary.total.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                </div>
                            </div>

                            {/* Collapsible Payment Instructions (Image 2 right screen bottom) */}
                            <div className="mt-4 rounded-2xl bg-[#F1F5F4] dark:bg-slate-900/70 p-4">
                                <button
                                    type="button"
                                    onClick={() => setShowInstructions(!showInstructions)}
                                    className="w-full flex items-center justify-between text-xs font-bold text-[#111827] dark:text-white"
                                >
                                    <span>Ver Instrucciones de Pago</span>
                                    <span>{showInstructions ? '▴' : '▾'}</span>
                                </button>

                                {showInstructions && (
                                    <ol className="mt-3 space-y-1.5 text-[11px] text-slate-600 dark:text-slate-300 list-decimal list-inside leading-relaxed">
                                        {paymentMethod === 'qr' ? (
                                            <>
                                                <li>Abre la aplicación móvil de tu banco o billetera digital.</li>
                                                <li>Selecciona la opción <strong>Pagar con QR / Transferencia QR</strong>.</li>
                                                <li>Escanea el código QR mostrado en la pantalla del kiosco.</li>
                                                <li>Verifica el monto total (<strong>RD$ {summary.total.toLocaleString('es-DO', { minimumFractionDigits: 2 })}</strong>).</li>
                                                <li>Confirma la operación y presiona <strong>Confirmar Pago</strong> abajo.</li>
                                            </>
                                        ) : paymentMethod === 'card' ? (
                                            <>
                                                <li>Verifica que el monto en pantalla sea correcto.</li>
                                                <li>Acerca tu tarjeta con NFC o insértala en el PinPad inferior.</li>
                                                <li>Ingresa tu PIN bancario si el lector lo solicita.</li>
                                                <li>Presiona <strong>Confirmar Pago</strong> para emitir tu recibo con NCF.</li>
                                            </>
                                        ) : (
                                            <>
                                                <li>Toma nota o imprime tu código de turno rápido.</li>
                                                <li>Dirígete a la ventanilla de Caja Institucional.</li>
                                                <li>Indica tu matrícula <strong>{student?.student_code}</strong> al cajero.</li>
                                            </>
                                        )}
                                    </ol>
                                )}
                            </div>

                            {/* Confirm & Print Actions */}
                            <div className="mt-4 space-y-2.5">
                                <button
                                    type="button"
                                    disabled={selectedIds.length === 0 || processing}
                                    onClick={handleConfirmPayment}
                                    className={`w-full py-3.5 rounded-2xl font-bold text-sm transition-all active:scale-95 ${
                                        selectedIds.length > 0 && !processing
                                            ? 'bg-[#166534] hover:bg-[#14532d] text-white shadow-sm'
                                            : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'
                                    }`}
                                >
                                    {processing
                                        ? 'Verificando transacción...'
                                        : paymentMethod === 'cashier'
                                        ? 'Generar Ticket de Caja'
                                        : 'Confirmar Pago Realizado'}
                                </button>

                                <button
                                    type="button"
                                    onClick={() => window.print()}
                                    className="w-full py-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all active:scale-95"
                                >
                                    Imprimir Estado de Cuenta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </KioskLayout>
    );
}
