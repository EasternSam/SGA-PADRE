import React, { useState, useEffect, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

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
        <svg viewBox={`0 0 ${size} ${size}`} className="w-44 h-44 mx-auto" shapeRendering="crispEdges">
            {cells.map((row, rIdx) =>
                row.map((filled, cIdx) =>
                    filled ? <rect key={`${rIdx}-${cIdx}`} x={cIdx} y={rIdx} width={1} height={1} fill="#111827" /> : null
                )
            )}
        </svg>
    );
}

export default function Finances({ student, pendingPayments = [] }) {
    const { branding = {} } = usePage().props;
    const primaryColor = branding?.primary_color || '#211170';
    const secondaryColor = branding?.secondary_color || '#6b289e';

    const [selectedIds, setSelectedIds] = useState(() => pendingPayments.map((p) => p.id));
    const [paymentMethod, setPaymentMethod] = useState('qr'); // 'cashier' | 'card' | 'qr'
    const [secondsLeft, setSecondsLeft] = useState(14 * 60 + 59);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        setSelectedIds(pendingPayments.map((p) => p.id));
    }, [pendingPayments]);

    useEffect(() => {
        const timer = setInterval(() => {
            setSecondsLeft((prev) => (prev > 0 ? prev - 1 : 0));
        }, 1000);
        return () => clearInterval(timer);
    }, []);

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
        const total = selectedItems.reduce((acc, p) => acc + Number(p.amount ?? 0), 0);
        return { subtotal, discount, total };
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
                onFinish: () => setProcessing(false),
            }
        );
    };

    return (
        <KioskLayout student={student} backUrl="/kiosk/dashboard" showBack={true}>
            <Head title="Autopago - Kiosco" />

            <div className="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {/* Left: Cart & Payment Method */}
                <div className="lg:col-span-7 space-y-5">
                    <div className="rounded-[24px] kiosk-surface p-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-bold">Cargos Pendientes</h2>
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
                                    style={{ color: primaryColor }}
                                    className="text-xs font-bold"
                                >
                                    {selectedIds.length === pendingPayments.length ? 'Limpiar' : 'Todos'}
                                </button>
                            )}
                        </div>

                        {pendingPayments.length === 0 ? (
                            <div className="py-10 text-center rounded-2xl kiosk-key">
                                <div className="text-lg font-bold">Sin cargos pendientes</div>
                            </div>
                        ) : (
                            <div className="space-y-2.5">
                                {pendingPayments.map((item) => {
                                    const isSelected = selectedIds.includes(item.id);
                                    return (
                                        <div
                                            key={item.id}
                                            onClick={() => toggleSelect(item.id)}
                                            style={isSelected ? { borderColor: primaryColor } : undefined}
                                            className={`p-4 rounded-2xl kiosk-key border-2 transition-all cursor-pointer flex items-center justify-between gap-4 ${
                                                isSelected ? '' : 'opacity-70'
                                            }`}
                                        >
                                            <div>
                                                <div className="font-bold text-base">{item.concept}</div>
                                                <div style={{ color: primaryColor }} className="text-sm font-bold mt-0.5">
                                                    RD$ {Number(item.amount).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </div>
                                            </div>
                                            <span
                                                style={isSelected ? { backgroundColor: primaryColor, color: '#FFFFFF' } : undefined}
                                                className={`w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs ${
                                                    isSelected ? '' : 'kiosk-surface text-transparent'
                                                }`}
                                            >
                                                ✓
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {/* Purchase Details + 3 Payment Method Tiles */}
                    <div className="rounded-[24px] kiosk-surface p-6 space-y-4">
                        <div className="space-y-2 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="opacity-70">Subtotal</span>
                                <span className="font-semibold">
                                    RD$ {summary.subtotal.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                            {summary.discount > 0 && (
                                <div className="flex items-center justify-between">
                                    <span className="opacity-70">Descuento</span>
                                    <span className="font-semibold">
                                        -RD$ {summary.discount.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            )}
                            <div className="pt-2 border-t border-neutral-200 dark:border-[#262626] flex items-center justify-between text-base font-bold">
                                <span>Total</span>
                                <span style={{ color: primaryColor }}>
                                    RD$ {summary.total.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                        </div>

                        <div className="grid grid-cols-3 gap-2.5 pt-2">
                            {[
                                { id: 'cashier', label: 'En Caja' },
                                { id: 'card', label: 'Tarjeta' },
                                { id: 'qr', label: 'Código QR' },
                            ].map((m) => {
                                const active = paymentMethod === m.id;
                                return (
                                    <button
                                        key={m.id}
                                        type="button"
                                        onClick={() => setPaymentMethod(m.id)}
                                        style={active ? { borderColor: primaryColor, color: primaryColor } : undefined}
                                        className={`p-3.5 rounded-2xl border-2 text-center font-semibold text-xs sm:text-sm transition-all active:scale-95 kiosk-key ${
                                            active ? '' : 'opacity-75'
                                        }`}
                                    >
                                        {m.label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Right: QR / Terminal Payment Confirmation */}
                <div className="lg:col-span-5 rounded-[24px] kiosk-surface p-6 space-y-4">
                    <div
                        style={{
                            background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
                        }}
                        className="rounded-2xl p-6 text-white text-center"
                    >
                        <div className="inline-flex items-center gap-1.5 mb-4">
                            <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                {mins[0]}
                            </span>
                            <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                {mins[1]}
                            </span>
                            <span className="font-bold">:</span>
                            <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                {secs[0]}
                            </span>
                            <span className="w-8 h-9 rounded-lg bg-white/20 font-mono font-bold text-base flex items-center justify-center">
                                {secs[1]}
                            </span>
                        </div>

                        <div className="bg-white rounded-2xl p-4 max-w-[210px] mx-auto">
                            {paymentMethod === 'qr' && (
                                <KioskQrCodeSvg
                                    value={`KIOSK|${student?.student_code || 'EST'}|${summary.total}|${selectedIds.join(',')}`}
                                />
                            )}
                            {paymentMethod === 'card' && (
                                <div className="py-10 px-2 text-[#111827] font-bold text-sm">
                                    Acerca o inserta tu tarjeta
                                </div>
                            )}
                            {paymentMethod === 'cashier' && (
                                <div className="py-8 px-2 text-[#111827]">
                                    <div className="text-3xl font-mono font-black">
                                        C-{String((student?.id || 12) % 90 + 10)}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-2xl kiosk-key p-4 text-center">
                        <div className="text-xs opacity-60">Total</div>
                        <div style={{ color: primaryColor }} className="text-2xl font-black mt-0.5">
                            RD$ {summary.total.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                        </div>
                    </div>

                    <button
                        type="button"
                        disabled={selectedIds.length === 0 || processing}
                        onClick={handleConfirmPayment}
                        style={
                            selectedIds.length > 0 && !processing
                                ? {
                                      background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
                                      color: '#FFFFFF',
                                  }
                                : undefined
                        }
                        className={`w-full py-4 rounded-2xl font-bold text-sm transition-all active:scale-95 ${
                            selectedIds.length > 0 && !processing
                                ? ''
                                : 'kiosk-surface opacity-45 cursor-not-allowed'
                        }`}
                    >
                        {processing ? 'Procesando...' : 'Confirmar Pago'}
                    </button>
                </div>
            </div>
        </KioskLayout>
    );
}
