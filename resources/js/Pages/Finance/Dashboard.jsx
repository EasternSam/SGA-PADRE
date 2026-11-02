import React, { useState, useEffect, useRef, useMemo, useCallback } from 'react';
import { Head, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

const TOP = 12;
const FILL = 0.58;
const MAX_BAR = 28;
const RADIUS = 4;

function formatCurrency(val, currency = 'DOP') {
    const num = Number(val || 0);
    const prefix = currency === 'USD' ? 'US$' : currency === 'EUR' ? '€' : 'RD$';
    return `${prefix} ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatCompact(value) {
    const num = Number(value || 0);
    if (num >= 1000000) return (num / 1000000).toFixed(1).replace('.0', '') + 'M';
    if (num >= 1000) return (num / 1000).toFixed(0) + 'K';
    return String(Math.round(num));
}

function scaleFor(max) {
    const safe = Math.max(max, 1);
    const magnitude = 10 ** Math.floor(Math.log10(safe / 4));
    for (const factor of [1, 2, 3, 5, 10]) {
        const step = factor * magnitude;
        const rows = Math.ceil(safe / step);
        if (rows <= 4) {
            return {
                top: rows * step,
                ticks: Array.from({ length: rows + 1 }, (_, row) => row * step),
            };
        }
    }
    return { top: safe, ticks: [0, safe] };
}

function barPath(x, w, h, base, radius = RADIUS) {
    if (w <= 0 || h <= 0.01) {
        return `M${x.toFixed(2)} ${base.toFixed(2)}H${(x + Math.max(w, 0)).toFixed(2)}Z`;
    }
    const r = Math.min(radius, w / 2, h);
    const top = base - h;
    const f = (val) => Number(val).toFixed(2);
    return `M${f(x)} ${f(base)}V${f(top + r)}A${f(r)} ${f(r)} 0 0 1 ${f(x + r)} ${f(top)}H${f(x + w - r)}A${f(r)} ${f(r)} 0 0 1 ${f(x + w)} ${f(top + r)}V${f(base)}Z`;
}

/**
 * Bencho ProgressTicks Interactive Equalizer Component (1:1 Gridbase Bills)
 */
function BenchoTickWidget({ basePct = 0, caption = '' }) {
    const OH = 34;
    const rowRef = useRef(null);
    const [displayPct, setDisplayPct] = useState(0);
    const [cursorIdx, setCursorIdx] = useState(null);
    const [entered, setEntered] = useState(false);
    const isDraggingRef = useRef(false);

    useEffect(() => {
        let rafId = null;
        let startTime = null;
        const baseR = Math.round((basePct / 100) * OH);
        const duration = Math.min(750, 250 + baseR * 20);

        const step = (ts) => {
            if (!startTime) startTime = ts;
            const p = Math.min(1, (ts - startTime) / duration);
            const eased = 1 - Math.pow(1 - p, 3);
            setDisplayPct(Math.round(eased * basePct));
            if (p < 1) {
                rafId = requestAnimationFrame(step);
            } else {
                setDisplayPct(basePct);
            }
        };
        rafId = requestAnimationFrame(step);
        const timer = setTimeout(() => setEntered(true), 40);

        return () => {
            if (rafId) cancelAnimationFrame(rafId);
            clearTimeout(timer);
        };
    }, [basePct]);

    const getTickFromClientX = useCallback((clientX) => {
        if (!rowRef.current) return null;
        const rect = rowRef.current.getBoundingClientRect();
        if (!rect || !rect.width) return null;
        const tick = Math.floor(((clientX - rect.left) / rect.width) * OH);
        return Math.min(OH - 1, Math.max(0, tick));
    }, []);

    const activeCount = cursorIdx !== null ? cursorIdx + 1 : Math.round((basePct / 100) * OH);
    const currentPct = cursorIdx !== null ? Math.round(((cursorIdx + 1) / OH) * 100) : displayPct;
    const delta = cursorIdx !== null ? currentPct - basePct : 0;

    return (
        <div className="tik-wrap">
            <div className="tik-head">
                <div className="tik-stat">
                    <span className="tik-fig">
                        <span>{currentPct}</span>
                        <i>%</i>
                    </span>
                    <span
                        className="tik-delta"
                        data-show={cursorIdx !== null && delta !== 0 ? 'true' : 'false'}
                        data-up={delta >= 0 ? 'true' : 'false'}
                    >
                        {delta > 0 ? `+${delta}` : delta}
                    </span>
                </div>
                <span className="db-kpi-caption-sub">{caption}</span>
            </div>
            <div
                ref={rowRef}
                className="tick-row"
                onPointerDown={(e) => {
                    isDraggingRef.current = true;
                    try {
                        e.currentTarget.setPointerCapture(e.pointerId);
                    } catch (_) {}
                    setCursorIdx(getTickFromClientX(e.clientX));
                }}
                onPointerMove={(e) => {
                    if (e.pointerType === 'mouse' || isDraggingRef.current) {
                        setCursorIdx(getTickFromClientX(e.clientX));
                    }
                }}
                onPointerUp={() => {
                    isDraggingRef.current = false;
                    setCursorIdx(null);
                }}
                onPointerCancel={() => {
                    isDraggingRef.current = false;
                    setCursorIdx(null);
                }}
                onPointerLeave={() => {
                    if (!isDraggingRef.current) {
                        setCursorIdx(null);
                    }
                }}
            >
                {Array.from({ length: OH }).map((_, n) => {
                    const isOn = n < activeCount;
                    const isCursor = cursorIdx !== null && n === cursorIdx;
                    const isFrontier = cursorIdx === null && activeCount > 0 && n === activeCount - 1;
                    const baseH = 52 + Math.round(Math.sin(n / 3.1) * 14);
                    const h = baseH + (isOn ? 26 : 0);

                    return (
                        <div
                            key={n}
                            className="tick"
                            data-on={isOn ? 'true' : 'false'}
                            data-cursor={isCursor ? 'true' : 'false'}
                            data-frontier={isFrontier ? 'true' : 'false'}
                            style={{
                                height: entered ? `${h}%` : '0%',
                                transform: entered ? 'scaleY(1)' : 'scaleY(0)',
                                opacity: entered ? 1 : 0.15,
                                transitionDelay: entered && cursorIdx === null ? `${n * 10}ms` : '0ms',
                            }}
                        />
                    );
                })}
            </div>
        </div>
    );
}

/**
 * ITBIS / Fiscal Segmented Tick Equalizer (1:1 Gridbase Bills .db-tax-card)
 */
function TaxSegmentedEqualizer({ stats }) {
    const barRef = useRef(null);
    const [barWidth, setBarWidth] = useState(720);
    const [hoverTick, setHoverTick] = useState(null);
    const [entered, setEntered] = useState(false);
    const isDraggingRef = useRef(false);

    const v1 = Math.max(0, Number(stats?.tax_collected_this_month || 0));
    const v2 = Math.max(0, Number(stats?.tax_collected_last_month || 0));
    const v3 = Math.max(0, Number(stats?.tax_collected_total || 0));
    const v3Rest = Math.max(0, v3 - v1 - v2);

    const totalPool = Math.max(1, v1 + v2 + v3Rest);
    const p1 = v1 / totalPool;
    const p2 = v2 / totalPool;
    const p3 = v3Rest / totalPool;

    const maxVal = Math.max(v1, v2, v3Rest, 1);
    const baseH1 = Math.round(22 + (v1 / maxVal) * 22);
    const baseH2 = Math.round(20 + (v2 / maxVal) * 22);
    const baseH3 = Math.round(18 + (v3Rest / maxVal) * 20);

    useEffect(() => {
        const timer = setTimeout(() => setEntered(true), 60);
        if (!barRef.current || !window.ResizeObserver) return () => clearTimeout(timer);
        const ro = new ResizeObserver((entries) => {
            for (const entry of entries) {
                const w = Math.round(entry.contentRect?.width || barRef.current?.clientWidth || 720);
                if (w > 0) setBarWidth(w);
            }
        });
        ro.observe(barRef.current);
        return () => {
            clearTimeout(timer);
            ro.disconnect();
        };
    }, []);

    const isMobile = barWidth <= 640;
    const targetPitch = isMobile ? 11 : 16;
    const segGap = isMobile ? 10 : 16;
    const availableWidth = Math.max(80, barWidth - segGap * 2);
    const totalTicks = Math.max(isMobile ? 18 : 36, Math.floor(availableWidth / targetPitch));
    const minT = isMobile ? 3 : 5;
    const flexTicks = Math.max(0, totalTicks - minT * 3);
    const n1 = minT + Math.round(flexTicks * p1);
    const n2 = minT + Math.round(flexTicks * p2);
    const n3 = Math.max(minT, totalTicks - n1 - n2);

    const segments = useMemo(() => {
        const s0Start = 0;
        const s0End = n1 - 1;
        const s1Start = n1;
        const s1End = n1 + n2 - 1;
        const s2Start = n1 + n2;
        const s2End = n1 + n2 + n3 - 1;

        return [
            {
                idx: 0,
                start: s0Start,
                end: s0End,
                count: n1,
                baseH: baseH1,
                colorClass: 'tick-orange',
                name: 'Este Mes',
                val: v1,
                pct: (p1 * 100).toFixed(1),
            },
            {
                idx: 1,
                start: s1Start,
                end: s1End,
                count: n2,
                baseH: baseH2,
                colorClass: 'tick-yellow',
                name: 'Mes Pasado',
                val: v2,
                pct: (p2 * 100).toFixed(1),
            },
            {
                idx: 2,
                start: s2Start,
                end: s2End,
                count: n3,
                baseH: baseH3,
                colorClass: 'tick-muted',
                name: 'Total Acumulado',
                val: v3,
                pct: (p3 * 100).toFixed(1),
            },
        ];
    }, [n1, n2, n3, baseH1, baseH2, baseH3, v1, v2, v3, p1, p2, p3]);

    const activeSeg = useMemo(() => {
        if (hoverTick === null) return null;
        return segments.find((s) => hoverTick >= s.start && hoverTick <= s.end) || segments[0];
    }, [hoverTick, segments]);

    const handlePointer = useCallback(
        (clientX) => {
            if (!barRef.current) return;
            const rect = barRef.current.getBoundingClientRect();
            if (!rect.width) return;
            const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
            const total = n1 + n2 + n3;
            const tick = Math.min(total - 1, Math.max(0, Math.floor(ratio * total)));
            setHoverTick(tick);
        },
        [n1, n2, n3]
    );

    return (
        <div className="db-tax-card">
            <div className="db-tax-head">
                <div className="db-tax-title">
                    <span>ITBIS Facturado Neto</span>
                    <span
                        className="db-tax-live-pill"
                        style={{ opacity: activeSeg ? 1 : 0 }}
                    >
                        {activeSeg && (
                            <>
                                <span>{activeSeg.name}:</span>{' '}
                                <strong>{formatCurrency(activeSeg.val)}</strong>{' '}
                                <span style={{ opacity: 0.8 }}>({activeSeg.pct}%)</span>
                            </>
                        )}
                    </span>
                </div>
                <span className="db-tax-badge">18% ITBIS (606 / 607)</span>
            </div>

            <div className="db-tax-body">
                <div className="db-tax-bar-wrap">
                    <div
                        ref={barRef}
                        className={`db-tax-bar ${hoverTick !== null ? 'is-hovered' : ''}`}
                        onPointerDown={(e) => {
                            isDraggingRef.current = true;
                            try {
                                e.currentTarget.setPointerCapture(e.pointerId);
                            } catch (_) {}
                            handlePointer(e.clientX);
                        }}
                        onPointerMove={(e) => {
                            if (e.pointerType === 'mouse' || isDraggingRef.current) {
                                handlePointer(e.clientX);
                            }
                        }}
                        onPointerUp={() => {
                            isDraggingRef.current = false;
                            setHoverTick(null);
                        }}
                        onPointerCancel={() => {
                            isDraggingRef.current = false;
                            setHoverTick(null);
                        }}
                        onPointerLeave={() => {
                            if (!isDraggingRef.current) {
                                setHoverTick(null);
                            }
                        }}
                    >
                        {segments.map((seg, sIdx) => (
                            <React.Fragment key={seg.idx}>
                                {sIdx > 0 && <div className="db-tax-seg-gap" style={{ width: `${segGap}px` }} />}
                                <div
                                    className={`db-tax-segment ${activeSeg?.idx === seg.idx ? 'is-active' : ''}`}
                                    style={{ flex: seg.count }}
                                >
                                    {Array.from({ length: seg.count }).map((_, i) => {
                                        const globalIdx = seg.start + i;
                                        const wave = Math.round(Math.sin(i / 2.0) * 3.5);
                                        const baseH = Math.max(16, Math.min(48, seg.baseH + wave));
                                        const dist = hoverTick !== null ? Math.abs(globalIdx - hoverTick) : 999;
                                        const extra =
                                            dist === 0 ? 7 : dist <= 3 ? Math.round((4 - dist) * 1.8) : 0;
                                        const finalH = Math.min(50, baseH + extra);

                                        return (
                                            <span
                                                key={i}
                                                className={`db-tax-tick ${seg.colorClass}`}
                                                data-cursor={dist === 0 ? 'true' : undefined}
                                                style={{
                                                    height: `${finalH}px`,
                                                    transform: entered ? 'scaleY(1)' : 'scaleY(0)',
                                                    opacity: entered ? 1 : 0.12,
                                                }}
                                            />
                                        );
                                    })}
                                </div>
                            </React.Fragment>
                        ))}
                    </div>
                </div>

                <div className="db-tax-grid">
                    <div
                        className={`db-tax-col ${activeSeg?.idx === 0 ? 'is-active' : ''}`}
                        onMouseEnter={() =>
                            setHoverTick(Math.round((segments[0].start + segments[0].end) / 2))
                        }
                        onMouseLeave={() => setHoverTick(null)}
                    >
                        <div className="db-tax-col-header">
                            <span className="db-tax-dot dot-orange" />
                            <span className="db-tax-col-label">Este Mes</span>
                        </div>
                        <div className="db-tax-col-amount highlight">{formatCurrency(v1)}</div>
                        <div className="db-tax-col-sub">A declarar en período actual</div>
                    </div>

                    <div
                        className={`db-tax-col ${activeSeg?.idx === 1 ? 'is-active' : ''}`}
                        onMouseEnter={() =>
                            setHoverTick(Math.round((segments[1].start + segments[1].end) / 2))
                        }
                        onMouseLeave={() => setHoverTick(null)}
                    >
                        <div className="db-tax-col-header">
                            <span className="db-tax-dot dot-yellow" />
                            <span className="db-tax-col-label">Mes Pasado</span>
                        </div>
                        <div className="db-tax-col-amount">{formatCurrency(v2)}</div>
                        <div className="db-tax-col-sub">Período anterior</div>
                    </div>

                    <div
                        className={`db-tax-col ${activeSeg?.idx === 2 ? 'is-active' : ''}`}
                        onMouseEnter={() =>
                            setHoverTick(Math.round((segments[2].start + segments[2].end) / 2))
                        }
                        onMouseLeave={() => setHoverTick(null)}
                    >
                        <div className="db-tax-col-header">
                            <span className="db-tax-dot dot-muted" />
                            <span className="db-tax-col-label">Total Acumulado</span>
                        </div>
                        <div className="db-tax-col-amount">{formatCurrency(v3)}</div>
                        <div className="db-tax-col-sub">Historial fiscal registrado</div>
                    </div>
                </div>
            </div>

            {Number(stats?.tax_pending || 0) > 0 && (
                <div className="db-tax-footer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    {formatCurrency(stats.tax_pending)} en ITBIS registrado pendiente de pago
                </div>
            )}
        </div>
    );
}

/**
 * UI Arc Bar Chart (@uiarc/bar-chart) React Port (1:1 Gridbase Bills)
 */
function BillsUIBarChart({ monthlyData = [], series = 'net' }) {
    const plotRef = useRef(null);
    const [plotWidth, setPlotWidth] = useState(520);
    const [activeIndex, setActiveIndex] = useState(null);

    const seriesLabels = {
        net: 'Flujo Neto',
        revenue: 'Ingresos',
        expense: 'Gastos',
    };

    const data = useMemo(() => {
        return (monthlyData || []).map((m, idx) => {
            let val = 0;
            if (series === 'net') {
                val = Math.max(0, Number(m.revenue || 0) - Number(m.expense || 0));
            } else if (series === 'expense') {
                val = Number(m.expense || 0);
            } else {
                val = Number(m.revenue || 0);
            }
            return {
                key: m.month || `m-${idx}`,
                label: m.month || m.label,
                axisLabel: m.label || '',
                value: val,
            };
        });
    }, [monthlyData, series]);

    useEffect(() => {
        setActiveIndex(null);
    }, [series]);

    useEffect(() => {
        if (!plotRef.current) return;
        const updateW = () => {
            if (plotRef.current && plotRef.current.clientWidth > 0) {
                setPlotWidth(plotRef.current.clientWidth);
            }
        };
        updateW();
        if (!window.ResizeObserver) return;
        const ro = new ResizeObserver(updateW);
        ro.observe(plotRef.current);
        return () => ro.disconnect();
    }, []);

    const height = 190;
    const count = Math.max(data.length, 1);
    const base = height - 0.5;
    const availableHeight = height - TOP;
    const peak = data.reduce((max, d) => Math.max(max, d.value || 0), 0);
    const { top: scaleTop, ticks } = scaleFor(peak);
    const average = data.length ? data.reduce((s, d) => s + (d.value || 0), 0) / data.length : 0;

    const slotWidth = plotWidth / count;
    const barWidth = Math.min(slotWidth * FILL, MAX_BAR);
    const meanY = Math.round(base - (average / scaleTop) * availableHeight) + 0.5;

    let stride = 1;
    if (plotWidth < count * 36) stride = 2;
    if (plotWidth < count * 20) stride = 3;

    const activeItem = activeIndex !== null && data[activeIndex] ? data[activeIndex] : null;
    const headlineKind = activeItem
        ? seriesLabels[series]
        : `Promedio mensual (${seriesLabels[series]})`;
    const headlineVal = activeItem
        ? formatCurrency(activeItem.value)
        : formatCurrency(Math.round(average));
    const headlineWhen = activeItem ? activeItem.label : 'Últimos 12 meses';

    const pointAt = (clientX) => {
        if (!plotRef.current || !data.length) return null;
        const rect = plotRef.current.getBoundingClientRect();
        if (!rect.width) return null;
        const place = Math.floor(((rect.right - clientX) / rect.width) * data.length);
        return Math.min(data.length - 1, Math.max(0, data.length - 1 - place));
    };

    return (
        <div className="db-bar-chart-container uiarc-bar-chart-root">
            <figure className="uiarc-bar-chart" data-series={series}>
                <div className="uiarc-bar-chart-header" aria-hidden="true">
                    <span className="uiarc-bar-chart-kind">
                        <span className="uiarc-bar-chart-swap">{headlineKind}</span>
                    </span>
                    <span className="uiarc-bar-chart-value">
                        <span className="uiarc-bar-chart-roll">{headlineVal}</span>
                    </span>
                    <span className="uiarc-bar-chart-when">
                        <span className="uiarc-bar-chart-period">{headlineWhen}</span>
                    </span>
                </div>

                <div
                    className="uiarc-bar-chart-body"
                    data-scrubbing={activeIndex !== null ? 'true' : 'false'}
                >
                    <div
                        ref={plotRef}
                        className="uiarc-bar-chart-plot"
                        style={{ height: `${height}px` }}
                    >
                        <svg
                            className="uiarc-bar-chart-svg"
                            width="100%"
                            height={height}
                            role="img"
                        >
                            <g className="uiarc-bar-chart-grid-group">
                                {ticks.map((tickVal) => {
                                    if (tickVal <= 0) return null;
                                    const y =
                                        Math.round(base - (tickVal / scaleTop) * availableHeight) +
                                        0.5;
                                    return (
                                        <line
                                            key={tickVal}
                                            className="uiarc-bar-chart-grid"
                                            x1="0"
                                            x2="100%"
                                            y1={y}
                                            y2={y}
                                        />
                                    );
                                })}
                            </g>

                            <line
                                className="uiarc-bar-chart-cursor"
                                y1={TOP}
                                y2={height}
                                x1={
                                    activeIndex !== null
                                        ? ((activeIndex + 0.5) * slotWidth).toFixed(2)
                                        : 0
                                }
                                x2={
                                    activeIndex !== null
                                        ? ((activeIndex + 0.5) * slotWidth).toFixed(2)
                                        : 0
                                }
                                style={{ opacity: activeIndex !== null ? 1 : 0 }}
                            />

                            <g className="uiarc-bar-chart-bars-group">
                                {data.map((item, index) => {
                                    const centerX = (index + 0.5) * slotWidth;
                                    const barX = centerX - barWidth / 2;
                                    const targetHeight = Math.max(
                                        0,
                                        (item.value / scaleTop) * availableHeight
                                    );
                                    return (
                                        <path
                                            key={item.key}
                                            className="uiarc-bar-chart-bar"
                                            data-active={
                                                activeIndex === index ? 'true' : undefined
                                            }
                                            d={barPath(barX, barWidth, targetHeight, base)}
                                        />
                                    );
                                })}
                            </g>

                            <line
                                className="uiarc-bar-chart-baseline"
                                x1="0"
                                x2="100%"
                                y1={base}
                                y2={base}
                            />

                            <line
                                className="uiarc-bar-chart-mean"
                                x1="0"
                                x2="100%"
                                y1={meanY}
                                y2={meanY}
                            />
                        </svg>
                    </div>

                    <div className="uiarc-bar-chart-gutter" aria-hidden="true">
                        {ticks.map((tickVal) => {
                            const y =
                                Math.round(base - (tickVal / scaleTop) * availableHeight) + 0.5;
                            return (
                                <span
                                    key={tickVal}
                                    className="uiarc-bar-chart-tick"
                                    style={{ top: `${y}px` }}
                                >
                                    <span className="uiarc-bar-chart-tick-text">
                                        {formatCompact(tickVal)}
                                    </span>
                                </span>
                            );
                        })}
                        <span
                            className="uiarc-bar-chart-mean-label"
                            style={{ top: `${meanY}px` }}
                        >
                            <span className="uiarc-bar-chart-tick-text">
                                Avg {formatCompact(average)}
                            </span>
                        </span>
                    </div>

                    <div className="uiarc-bar-chart-axis" aria-hidden="true">
                        {data.map((item, index) => {
                            const fromEnd = count - 1 - index;
                            if (fromEnd % stride !== 0) return null;
                            const centerX = (index + 0.5) * slotWidth;
                            return (
                                <span
                                    key={item.key}
                                    className="uiarc-bar-chart-axis-label"
                                    style={{ left: `${centerX}px` }}
                                >
                                    {item.axisLabel || item.label}
                                </span>
                            );
                        })}
                    </div>

                    <div
                        className="uiarc-bar-chart-scrubber"
                        role="slider"
                        tabIndex={0}
                        aria-label="Flujo de Caja, interactuar por Mes"
                        onPointerDown={(e) => {
                            setActiveIndex(pointAt(e.clientX));
                        }}
                        onPointerMove={(e) => {
                            if (e.pointerType === 'mouse' || e.buttons) {
                                setActiveIndex(pointAt(e.clientX));
                            }
                        }}
                        onPointerUp={(e) => {
                            if (e.pointerType !== 'mouse') setActiveIndex(null);
                        }}
                        onPointerCancel={() => setActiveIndex(null)}
                        onPointerLeave={(e) => {
                            if (e.pointerType === 'mouse') setActiveIndex(null);
                        }}
                    />
                </div>
            </figure>
        </div>
    );
}

export default function FinanceDashboard({
    stats = {},
    monthly_data = [],
    recent_invoices = [],
    overdue_invoices = [],
    exchange_rates = {},
}) {
    const { auth } = usePage().props;
    const [activeSeries, setActiveSeries] = useState('net');

    const firstName = ((auth?.user?.name || 'Administrador').split(' ')[0] || 'Admin').trim();
    const dateStr = useMemo(() => {
        return new Date().toLocaleDateString('es-DO', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    }, []);

    const cur = monthly_data[monthly_data.length - 1] || { revenue: 0, expense: 0 };
    const prev = monthly_data[monthly_data.length - 2] || { revenue: 0, expense: 0 };

    let trendPct = 0;
    let isTrendUp = true;
    if (prev.revenue > 0) {
        const diff = cur.revenue - prev.revenue;
        trendPct = Math.round((diff / prev.revenue) * 100);
        isTrendUp = diff >= 0;
    } else if (cur.revenue > 0) {
        trendPct = 100;
    }

    const invCur = Number(stats.invoiced_amount_this_month) || 0;
    const invPrev = Number(stats.invoiced_amount_last_month) || 0;
    let invTrendPct = 0;
    let isInvTrendUp = true;
    if (invPrev > 0) {
        const diff = invCur - invPrev;
        invTrendPct = Math.round((diff / invPrev) * 100);
        isInvTrendUp = diff >= 0;
    } else if (invCur > 0) {
        invTrendPct = 100;
    }

    const revThisMonth = Number(stats.revenue_this_month || cur.revenue || 0);
    const expThisMonth = Number(cur.expense || 0);
    let margenPct = 0;
    if (revThisMonth > 0) {
        if (expThisMonth > 0) {
            margenPct = Math.min(
                100,
                Math.max(0, Math.round(((revThisMonth - expThisMonth) / revThisMonth) * 100))
            );
        } else {
            margenPct = 100;
        }
    }

    let facturadoPct = 0;
    if (invPrev > 0) {
        facturadoPct = Math.min(100, Math.max(0, Math.round((invCur / invPrev) * 100)));
    } else if (invCur > 0) {
        facturadoPct = 100;
    }

    const totalCobro =
        Number(stats.invoiced_amount_this_month) ||
        revThisMonth + Number(stats.pending_amount || 0) ||
        0;
    let cobroPct = 0;
    if (totalCobro > 0) {
        const collected = Math.max(0, totalCobro - Number(stats.pending_amount || 0));
        cobroPct = Math.min(100, Math.max(0, Math.round((collected / totalCobro) * 100)));
    }

    const avatarColors = [
        'db-av-green',
        'db-av-blue',
        'db-av-purple',
        'db-av-amber',
        'db-av-red',
        'db-av-gray',
    ];

    const statusClasses = {
        paid: 'badge-paid',
        accepted: 'badge-paid',
        sent: 'badge-sent',
        viewed: 'badge-viewed',
        partial: 'badge-partial',
        overdue: 'badge-overdue',
        draft: 'badge-draft',
        cancelled: 'badge-cancelled',
    };

    const statusLabels = {
        paid: 'Pagada',
        accepted: 'Aceptada',
        sent: 'Pendiente',
        viewed: 'Vista',
        partial: 'Parcial',
        overdue: 'Vencida',
        draft: 'Emitida',
        cancelled: 'Anulada',
    };

    return (
        <AuthenticatedLayout title="Panel Financiero">
            <Head title="Panel Financiero — Gridbase Bills" />

            <style>{`
                .db {
                    --bg-card: #ffffff;
                    --bg-hover: #f4f4f5;
                    --color-border: #e4e4e7;
                    --color-text-primary: #18181b;
                    --color-text-secondary: #52525b;
                    --color-text-muted: #71717a;
                    --color-success-bg: rgba(16, 185, 129, 0.12);
                    --color-success-text: #059669;
                    --color-success-icon: #10b981;
                    --color-danger-bg: rgba(239, 68, 68, 0.12);
                    --color-danger-text: #dc2626;
                    --color-danger-icon: #ef4444;
                    --radius-md: 10px;
                    --radius-lg: 14px;
                    --radius-xl: 20px;
                    --radius-full: 9999px;
                    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.03);

                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                    width: 100%;
                    padding-bottom: 24px;
                    animation: dbIn 0.35s ease both;
                }

                .dark .db,
                [data-theme="dark"] .db {
                    --bg-card: #0e0f11;
                    --bg-hover: rgba(255, 255, 255, 0.04);
                    --color-border: rgba(255, 255, 255, 0.07);
                    --color-text-primary: #f2f1ee;
                    --color-text-secondary: #a1a1aa;
                    --color-text-muted: #71717a;
                    --color-success-bg: rgba(16, 185, 129, 0.15);
                    --color-success-text: #34d399;
                    --color-success-icon: #10b981;
                    --color-danger-bg: rgba(239, 68, 68, 0.15);
                    --color-danger-text: #f87171;
                    --color-danger-icon: #ef4444;
                    --shadow-sm: none;
                }

                @keyframes dbIn {
                    from { opacity: 0; transform: translateY(6px); }
                    to   { opacity: 1; transform: none; }
                }

                .db-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    flex-wrap: wrap;
                    gap: 12px;
                }
                .db-header-left {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    flex-wrap: wrap;
                }
                .db-welcome {
                    font-size: 28px;
                    font-weight: 700;
                    color: var(--color-text-primary);
                    letter-spacing: -0.025em;
                    line-height: 1.15;
                    display: inline-block;
                }
                .db-header-right {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .db-date {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    font-size: 13px;
                    font-weight: 500;
                    color: var(--color-text-secondary);
                    background: var(--bg-card);
                    border: 1px solid var(--color-border);
                    border-radius: var(--radius-md);
                    padding: 7px 14px;
                }
                .db-date svg { width: 15px; height: 15px; color: var(--color-text-muted); }

                .db-kpi-row {
                    display: grid;
                    grid-template-columns: 1.4fr 1fr 1fr;
                    gap: 16px;
                }
                .db-kpi {
                    background: var(--bg-card);
                    border: 1px solid var(--color-border);
                    border-radius: 20px;
                    padding: 22px 24px;
                    box-shadow: var(--shadow-sm);
                    display: flex;
                    flex-direction: column;
                    transition: border-color 0.2s ease, background-color 0.2s ease;
                }
                .dark .db-kpi:hover {
                    border-color: rgba(255, 255, 255, 0.12);
                }
                .db-kpi-top {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 14px;
                    gap: 8px;
                }
                .db-kpi-label {
                    font-size: 13px;
                    font-weight: 500;
                    color: var(--color-text-secondary);
                }
                .db-kpi-caption-sub {
                    font-size: 11px;
                    font-weight: 600;
                    color: var(--color-text-muted);
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    opacity: 0.75;
                }
                .db-kpi-quick-actions {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    flex-wrap: wrap;
                }
                .db-kpi-action-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    padding: 4px 10px;
                    font-size: 11.5px;
                    font-weight: 600;
                    border-radius: 9999px;
                    background: var(--bg-hover);
                    color: var(--color-text-secondary);
                    border: 1px solid var(--color-border);
                    text-decoration: none;
                    transition: all 0.16s ease;
                    line-height: 1.2;
                }
                .db-kpi-action-pill:hover {
                    background: var(--color-border);
                    color: var(--color-text-primary);
                    transform: translateY(-1px);
                }
                .db-kpi-action-pill svg {
                    width: 12px;
                    height: 12px;
                    opacity: 0.8;
                }
                .db-kpi-amount {
                    font-size: 30px;
                    font-weight: 800;
                    color: var(--color-text-primary);
                    letter-spacing: -0.03em;
                    line-height: 1.1;
                }
                .db-kpi-main-amount-row { display: flex; align-items: baseline; flex-wrap: wrap; }
                .db-kpi-main .db-kpi-amount { font-size: 34px; }
                .db-kpi-main .db-kpi-meta {
                    font-size: 13px;
                    color: var(--color-text-muted);
                    margin-top: 6px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .db-kpi-tag {
                    display: inline-flex;
                    align-items: center;
                    gap: 3px;
                    font-size: 12px;
                    font-weight: 600;
                    padding: 3px 8px;
                    border-radius: var(--radius-full);
                    margin-left: 10px;
                    vertical-align: middle;
                }
                .db-kpi-tag.up {
                    background: var(--color-success-bg);
                    color: var(--color-success-text);
                }
                .db-kpi-tag.down {
                    background: var(--color-danger-bg);
                    color: var(--color-danger-text);
                }
                .db-kpi-tag svg { width: 11px; height: 11px; }
                .db-kpi-compare {
                    font-size: 12px;
                    color: var(--color-text-muted);
                    margin-top: 8px;
                }
                .db-kpi-compare .trend-val { font-weight: 600; }
                .db-kpi-compare .trend-val.up { color: var(--color-success-icon); }
                .db-kpi-compare .trend-val.down { color: var(--color-danger-icon); }

                /* Bencho ProgressTicks */
                .tik-wrap {
                    margin-top: auto;
                    padding-top: 16px;
                    display: flex;
                    flex-direction: column;
                    width: 100%;
                }
                .tik-head {
                    align-items: baseline;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    display: flex;
                }
                .tik-stat {
                    display: flex;
                    align-items: baseline;
                    gap: 8px;
                }
                .tik-fig {
                    letter-spacing: -0.035em;
                    color: var(--color-text-primary);
                    font-variant-numeric: tabular-nums;
                    font-size: 26px;
                    font-weight: 700;
                    line-height: 1;
                }
                .tik-fig i {
                    opacity: 0.35;
                    margin-left: 2px;
                    font-size: 15px;
                    font-style: normal;
                    font-weight: 500;
                }
                .tik-delta {
                    letter-spacing: 0.01em;
                    color: var(--color-text-muted);
                    opacity: 0;
                    font-variant-numeric: tabular-nums;
                    font-size: 13px;
                    font-weight: 600;
                    transition: opacity 0.14s ease, color 0.14s ease;
                }
                .tik-delta[data-show="true"] { opacity: 1; }
                .tik-delta[data-up="true"] { color: #10b981; }
                .tik-delta[data-up="false"] { color: #ef4444; }

                .tick-row {
                    touch-action: pan-y;
                    border-radius: 6px;
                    align-items: flex-end;
                    gap: 2.5px;
                    height: 46px;
                    display: flex;
                    width: 100%;
                    overflow: hidden;
                    cursor: crosshair;
                    user-select: none;
                    -webkit-user-select: none;
                    padding: 2px 0;
                }
                .tick {
                    background: rgba(23, 23, 26, 0.12);
                    border-radius: 2px;
                    flex: 1;
                    min-width: 2px;
                    transform-origin: bottom;
                    transition: background 0.16s ease, height 0.32s cubic-bezier(0.22, 1, 0.36, 1), transform 0.32s cubic-bezier(0.34, 1.45, 0.64, 1), opacity 0.2s ease;
                }
                .dark .tick {
                    background: rgba(242, 241, 238, 0.16);
                }
                .tick[data-on="true"] {
                    background: #18181b;
                }
                .dark .tick[data-on="true"] {
                    background: #f2f1ee;
                }
                .tick[data-frontier="true"],
                .tick[data-cursor="true"] {
                    background: #10b981 !important;
                }

                /* Tax / ITBIS Widget */
                .db-tax-card {
                    background: var(--bg-card);
                    border: 1px solid var(--color-border);
                    border-radius: var(--radius-xl);
                    box-shadow: var(--shadow-sm);
                    overflow: hidden;
                }
                .db-tax-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 18px 24px 14px;
                    border-bottom: 1px solid var(--color-border);
                }
                .db-tax-title {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 15px;
                    font-weight: 700;
                    color: var(--color-text-primary);
                }
                .db-tax-badge {
                    font-size: 11px;
                    font-weight: 600;
                    padding: 4px 10px;
                    border-radius: var(--radius-full);
                    background: #FEF3C7;
                    color: #92400E;
                    border: 1px solid #FDE68A;
                }
                .dark .db-tax-badge {
                    background: rgba(251, 191, 36, 0.12);
                    color: #FCD34D;
                    border-color: rgba(251, 191, 36, 0.2);
                }
                .db-tax-live-pill {
                    font-size: 11px;
                    font-weight: 600;
                    padding: 3px 9px;
                    border-radius: var(--radius-full);
                    background: var(--bg-hover);
                    color: var(--color-text-secondary);
                    border: 1px solid var(--color-border);
                    margin-left: 8px;
                    transition: opacity 0.2s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }
                .db-tax-live-pill strong { color: var(--color-text-primary); }
                .db-tax-body { padding: 22px 24px; }
                .db-tax-bar-wrap { margin-bottom: 22px; width: 100%; }
                .db-tax-bar {
                    display: flex;
                    align-items: flex-end;
                    height: 52px;
                    width: 100%;
                    cursor: crosshair;
                    user-select: none;
                    padding: 2px 0;
                }
                .db-tax-segment {
                    display: flex;
                    align-items: flex-end;
                    gap: 3px;
                    height: 52px;
                    min-width: 0;
                    transition: opacity 0.2s ease;
                }
                .db-tax-bar.is-hovered .db-tax-segment:not(.is-active) {
                    opacity: 0.35;
                }
                .db-tax-seg-gap { flex-shrink: 0; }
                .db-tax-tick {
                    flex: 1;
                    min-width: 4px;
                    border-radius: 2.5px;
                    transform-origin: bottom;
                    transition: height 0.28s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.16s ease, transform 0.36s cubic-bezier(0.34, 1.45, 0.64, 1), opacity 0.2s ease;
                }
                .db-tax-tick.tick-orange { background: #F97316; }
                .dark .db-tax-tick.tick-orange { background: #FB923C; }
                .db-tax-tick.tick-yellow { background: #F59E0B; }
                .dark .db-tax-tick.tick-yellow { background: #FBBF24; }
                .db-tax-tick.tick-muted { background: #E4E4E7; }
                .dark .db-tax-tick.tick-muted { background: #3F3F46; }
                .db-tax-tick[data-cursor="true"] {
                    background: #18181b !important;
                    transform: scaleY(1.08);
                }
                .dark .db-tax-tick[data-cursor="true"] {
                    background: #FFFFFF !important;
                    box-shadow: 0 0 8px rgba(255, 255, 255, 0.85);
                }
                .db-tax-grid {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 20px;
                }
                .db-tax-col {
                    display: flex;
                    flex-direction: column;
                    min-width: 0;
                    padding: 8px 12px;
                    margin: -8px -12px;
                    border-radius: var(--radius-lg);
                    transition: background-color 0.2s ease, transform 0.2s ease;
                    cursor: pointer;
                }
                .db-tax-col:hover,
                .db-tax-col.is-active {
                    background: var(--bg-hover);
                    transform: translateY(-2px);
                }
                .db-tax-col-header {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 6px;
                }
                .db-tax-dot {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    flex-shrink: 0;
                }
                .db-tax-dot.dot-orange { background: #F97316; }
                .db-tax-dot.dot-yellow { background: #F59E0B; }
                .db-tax-dot.dot-muted { background: #A1A1AA; }
                .db-tax-col-label {
                    font-size: 13.5px;
                    font-weight: 500;
                    color: var(--color-text-secondary);
                }
                .db-tax-col-amount {
                    font-size: 21px;
                    font-weight: 700;
                    color: var(--color-text-primary);
                    letter-spacing: -0.02em;
                    font-variant-numeric: tabular-nums;
                    line-height: 1.25;
                }
                .db-tax-col-amount.highlight { color: #D97706; }
                .dark .db-tax-col-amount.highlight { color: #FCD34D; }
                .db-tax-col-sub {
                    font-size: 11px;
                    color: var(--color-text-muted);
                    margin-top: 4px;
                }
                .db-tax-footer {
                    padding: 12px 24px;
                    background: var(--bg-hover);
                    font-size: 12px;
                    color: var(--color-text-muted);
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    border-top: 1px solid var(--color-border);
                }
                .db-tax-footer svg { width: 13px; height: 13px; flex-shrink: 0; }

                /* Two-Column Body */
                .db-body {
                    display: grid;
                    grid-template-columns: 1fr 1.5fr;
                    gap: 16px;
                    align-items: start;
                }
                .db-col {
                    display: flex;
                    flex-direction: column;
                    gap: 16px;
                }
                .db-card {
                    background: var(--bg-card);
                    border: 1px solid var(--color-border);
                    border-radius: var(--radius-xl);
                    box-shadow: var(--shadow-sm);
                    overflow: hidden;
                }
                .db-card-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 18px 24px 14px;
                }
                .db-card-title {
                    font-size: 16px;
                    font-weight: 700;
                    color: var(--color-text-primary);
                }
                .db-card-badge {
                    font-size: 12px;
                    font-weight: 500;
                    color: var(--color-text-muted);
                    background: var(--bg-hover);
                    padding: 4px 12px;
                    border-radius: var(--radius-full);
                    border: 1px solid var(--color-border);
                }

                /* Activity Table */
                .db-activity-head {
                    display: grid;
                    grid-template-columns: 1fr auto auto;
                    gap: 16px;
                    padding: 0 24px 10px;
                    font-size: 11px;
                    font-weight: 600;
                    color: var(--color-text-muted);
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                }
                .db-activity-row {
                    display: grid;
                    grid-template-columns: 1fr auto auto;
                    gap: 16px;
                    align-items: center;
                    padding: 14px 24px;
                    border-top: 1px solid var(--color-border);
                    text-decoration: none;
                    color: inherit;
                    transition: background 0.12s ease;
                    cursor: pointer;
                }
                .db-activity-row:hover { background: var(--bg-hover); }
                .db-activity-who {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    min-width: 0;
                }
                .db-activity-avatar {
                    width: 36px;
                    height: 36px;
                    border-radius: var(--radius-full);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 700;
                    font-size: 13px;
                    flex-shrink: 0;
                    color: #FFFFFF;
                }
                .db-av-green  { background: #059669; }
                .db-av-blue   { background: #2563EB; }
                .db-av-purple { background: #7C3AED; }
                .db-av-amber  { background: #D97706; }
                .db-av-red    { background: #DC2626; }
                .db-av-gray   { background: #6B7280; }
                .db-activity-name {
                    font-size: 13px;
                    font-weight: 600;
                    color: var(--color-text-primary);
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .db-activity-sub {
                    font-size: 12px;
                    color: var(--color-text-muted);
                    margin-top: 1px;
                }
                .db-activity-date {
                    font-size: 13px;
                    color: var(--color-text-secondary);
                    white-space: nowrap;
                }
                .db-activity-amount {
                    font-size: 14px;
                    font-weight: 700;
                    white-space: nowrap;
                    text-align: right;
                    font-variant-numeric: tabular-nums;
                }
                .db-amount-pos { color: var(--color-success-icon); }
                .db-amount-neg { color: var(--color-text-primary); }
                .db-amount-danger { color: #dc2626; font-weight: 700; }
                .db-amount-zero { color: var(--color-text-muted); font-weight: 600; }

                /* Badges */
                .bills-badge {
                    display: inline-flex;
                    align-items: center;
                    border-radius: 9999px;
                    font-size: 10px;
                    font-weight: 600;
                    padding: 2px 7px;
                    line-height: 1.2;
                }
                .badge-paid { background: var(--color-success-bg); color: var(--color-success-text); }
                .badge-sent, .badge-partial { background: rgba(245, 158, 11, 0.14); color: #d97706; }
                .dark .badge-sent, .dark .badge-partial { color: #fbbf24; }
                .badge-overdue, .badge-cancelled { background: var(--color-danger-bg); color: var(--color-danger-text); }
                .badge-draft { background: var(--bg-hover); color: var(--color-text-secondary); }
                .badge-active { background: var(--color-success-bg); color: var(--color-success-text); font-size: 11px; padding: 3px 9px; border-radius: 9999px; font-weight: 600; }

                /* Segmented Control (Cashflow Tabs) */
                .segmented-control {
                    display: inline-flex;
                    align-items: center;
                    background-color: #F1F2F4;
                    border: 1px solid rgba(0, 0, 0, 0.06);
                    border-radius: 14px;
                    padding: 3px;
                    gap: 2px;
                }
                .dark .segmented-control {
                    background-color: #1c1d21;
                    border-color: rgba(255, 255, 255, 0.06);
                }
                .segment-item {
                    height: 32px;
                    padding: 0 14px;
                    font-size: 12.5px;
                    font-weight: 600;
                    color: var(--color-text-secondary);
                    border-radius: 10px;
                    cursor: pointer;
                    transition: all 0.18s ease;
                    border: none;
                    background: transparent;
                }
                .segment-item.active {
                    background: #ffffff;
                    color: #111827;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
                }
                .dark .segment-item.active {
                    background: #0e0f11;
                    color: #ffffff;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.45);
                }

                /* UI Arc Bar Chart (@uiarc/bar-chart) */
                .db-bar-chart-container {
                    width: 100%;
                    padding: 18px 24px 16px;
                    box-sizing: border-box;
                }
                .uiarc-bar-chart {
                    display: grid;
                    min-width: 0;
                    gap: 16px;
                    margin: 0;
                    color: var(--color-text-primary);
                    user-select: none;
                }
                .uiarc-bar-chart-header {
                    display: grid;
                    min-width: 0;
                    gap: 2px;
                }
                .uiarc-bar-chart-kind {
                    font-size: 12.5px;
                    color: var(--color-text-secondary);
                    font-weight: 500;
                }
                .uiarc-bar-chart-when {
                    font-size: 12.5px;
                    color: var(--color-text-muted);
                }
                .uiarc-bar-chart-value {
                    display: flex;
                    align-items: baseline;
                    gap: 6px;
                    font-size: 26px;
                    font-weight: 700;
                    letter-spacing: -0.02em;
                    line-height: 1.15;
                    font-variant-numeric: tabular-nums;
                    color: var(--color-text-primary);
                }
                .uiarc-bar-chart-body {
                    position: relative;
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) 56px;
                    grid-template-rows: auto 24px;
                    min-width: 0;
                }
                .uiarc-bar-chart-plot {
                    position: relative;
                    grid-area: 1 / 1;
                    min-width: 0;
                }
                .uiarc-bar-chart-svg { display: block; overflow: hidden; }
                .uiarc-bar-chart-grid {
                    stroke: rgba(0, 0, 0, 0.06);
                    stroke-width: 1;
                    shape-rendering: crispEdges;
                }
                .dark .uiarc-bar-chart-grid { stroke: rgba(255, 255, 255, 0.08); }
                .uiarc-bar-chart-baseline {
                    stroke: rgba(0, 0, 0, 0.12);
                    stroke-width: 1;
                    shape-rendering: crispEdges;
                }
                .dark .uiarc-bar-chart-baseline { stroke: rgba(255, 255, 255, 0.16); }
                .uiarc-bar-chart-cursor {
                    stroke: rgba(0, 0, 0, 0.28);
                    stroke-width: 1;
                    shape-rendering: crispEdges;
                    pointer-events: none;
                    transition: opacity 0.1s ease;
                }
                .dark .uiarc-bar-chart-cursor { stroke: rgba(255, 255, 255, 0.38); }
                .uiarc-bar-chart-mean {
                    stroke: #10B981;
                    stroke-width: 1;
                    stroke-dasharray: 3 3;
                    shape-rendering: crispEdges;
                    opacity: 0.75;
                }
                .uiarc-bar-chart-bar {
                    fill: #059669;
                    opacity: 0.82;
                    cursor: pointer;
                    transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), fill 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                }
                .dark .uiarc-bar-chart-bar {
                    fill: #10B981;
                    opacity: 0.75;
                }
                .uiarc-bar-chart-body[data-scrubbing="true"] .uiarc-bar-chart-bar { opacity: 0.26; }
                .uiarc-bar-chart-body .uiarc-bar-chart-bar[data-active="true"] {
                    opacity: 1 !important;
                    fill: #10B981 !important;
                    filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.45));
                }
                .uiarc-bar-chart[data-series="expense"] .uiarc-bar-chart-bar { fill: #2563EB; }
                .dark .uiarc-bar-chart[data-series="expense"] .uiarc-bar-chart-bar { fill: #3B82F6; }
                .uiarc-bar-chart[data-series="expense"] .uiarc-bar-chart-mean { stroke: #3B82F6; }
                .uiarc-bar-chart[data-series="expense"] .uiarc-bar-chart-bar[data-active="true"] {
                    fill: #3B82F6 !important;
                    filter: drop-shadow(0 0 6px rgba(59, 130, 246, 0.45));
                }
                .uiarc-bar-chart-gutter {
                    position: relative;
                    grid-area: 1 / 2;
                    min-width: 0;
                }
                .uiarc-bar-chart-tick,
                .uiarc-bar-chart-mean-label {
                    position: absolute;
                    left: 10px;
                    display: block;
                    height: 0;
                    white-space: nowrap;
                    pointer-events: none;
                    font-variant-numeric: tabular-nums;
                }
                .uiarc-bar-chart-tick-text {
                    display: block;
                    transform: translateY(-50%);
                    color: var(--color-text-muted);
                    font-size: 10.5px;
                    line-height: 1;
                }
                .uiarc-bar-chart-mean-label .uiarc-bar-chart-tick-text {
                    color: #047857;
                    font-weight: 600;
                    font-size: 10px;
                    background: rgba(5, 150, 105, 0.1);
                    padding: 2px 5px;
                    border-radius: 4px;
                }
                .dark .uiarc-bar-chart-mean-label .uiarc-bar-chart-tick-text {
                    color: #10B981;
                    background: rgba(16, 185, 129, 0.12);
                }
                .uiarc-bar-chart[data-series="expense"] .uiarc-bar-chart-mean-label .uiarc-bar-chart-tick-text {
                    color: #1D4ED8;
                    background: rgba(37, 99, 235, 0.1);
                }
                .dark .uiarc-bar-chart[data-series="expense"] .uiarc-bar-chart-mean-label .uiarc-bar-chart-tick-text {
                    color: #3B82F6;
                    background: rgba(59, 130, 246, 0.12);
                }
                .uiarc-bar-chart-axis {
                    position: relative;
                    grid-area: 2 / 1;
                    min-width: 0;
                    overflow: hidden;
                }
                .uiarc-bar-chart-axis-label {
                    position: absolute;
                    top: 8px;
                    color: var(--color-text-muted);
                    font-size: 11px;
                    line-height: 1;
                    white-space: nowrap;
                    transform: translateX(-50%);
                }
                .uiarc-bar-chart-scrubber {
                    position: absolute;
                    inset: 0;
                    outline: none;
                    touch-action: pan-y;
                    user-select: none;
                    cursor: crosshair;
                    z-index: 10;
                }

                /* Overdue Section */
                .db-overdue-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                }
                .db-overdue-cell {
                    padding: 18px 20px;
                    border-top: 1px solid var(--color-border);
                    text-decoration: none;
                    color: inherit;
                    transition: background 0.12s ease;
                }
                .db-overdue-cell:first-child { border-right: 1px solid var(--color-border); }
                .db-overdue-cell:hover { background: var(--bg-hover); }
                .db-overdue-cell-title {
                    font-size: 13px;
                    font-weight: 700;
                    color: var(--color-text-primary);
                }
                .db-overdue-cell-sub {
                    font-size: 11px;
                    color: var(--color-text-muted);
                    margin-top: 1px;
                }
                .db-overdue-cell-amount {
                    font-size: 16px;
                    font-weight: 800;
                    color: var(--color-text-primary);
                    margin-top: 6px;
                    letter-spacing: -0.02em;
                }
                .db-overdue-bar-wrap {
                    margin-top: 8px;
                    width: 100%;
                    height: 6px;
                    background: var(--bg-hover);
                    border-radius: var(--radius-full);
                    overflow: hidden;
                }
                .db-overdue-bar {
                    height: 100%;
                    border-radius: var(--radius-full);
                    background: var(--color-danger-icon);
                }
                .db-overdue-item {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 14px 24px;
                    border-top: 1px solid var(--color-border);
                    text-decoration: none;
                    color: inherit;
                    transition: background 0.12s ease;
                }
                .db-overdue-item:hover { background: var(--bg-hover); }
                .db-pulse {
                    width: 7px;
                    height: 7px;
                    border-radius: 50%;
                    background: var(--color-danger-icon);
                    display: inline-block;
                    margin-right: 6px;
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6);
                    animation: dbPulse 2s infinite;
                }
                @keyframes dbPulse {
                    0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
                    70%  { box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
                }
                .db-empty {
                    padding: 32px 24px;
                    text-align: center;
                    color: var(--color-text-muted);
                    font-size: 13px;
                }

                @media (max-width: 1100px) {
                    .db-kpi-row { grid-template-columns: 1fr 1fr; }
                    .db-kpi-main { grid-column: 1 / -1; }
                }
                @media (max-width: 960px) {
                    .db-body { grid-template-columns: 1fr; }
                    .db-tax-grid { grid-template-columns: 1fr; gap: 14px; }
                }
            `}</style>

            <div className="bills-scope">
            <div className="workspace-panel">
            <div className="db">
                {/* ── Header (1:1 Gridbase Bills) ── */}
                <div className="db-header">
                    <div className="db-header-left">
                        <div className="db-welcome">Hola, {firstName}</div>
                        <PoweredByBills variant="badge" />
                    </div>
                    <div className="db-header-right">
                        <div className="db-date">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {dateStr}
                        </div>
                    </div>
                </div>

                {/* ── Top 3 KPI Cards (1:1 Gridbase Bills + Bencho ProgressTicks) ── */}
                <div className="db-kpi-row">
                    {/* Main Balance Card */}
                    <div className="db-kpi db-kpi-main">
                        <div className="db-kpi-top">
                            <span className="db-kpi-label">Ganancia Este Mes</span>
                            <div className="db-kpi-quick-actions">
                                <a href="/admin/finance/invoices" className="db-kpi-action-pill" title="Nueva Factura">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="12" y1="18" x2="12" y2="12" />
                                        <line x1="9" y1="15" x2="15" y2="15" />
                                    </svg>
                                    <span>Factura</span>
                                </a>
                                <a href="/admin/finance/expenses" className="db-kpi-action-pill" title="Registrar Gasto">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                    </svg>
                                    <span>Gasto</span>
                                </a>
                                <a href="/admin/pos" className="db-kpi-action-pill" title="Terminal de Caja POS">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2" />
                                        <line x1="2" y1="10" x2="22" y2="10" />
                                    </svg>
                                    <span>Caja POS</span>
                                </a>
                            </div>
                        </div>
                        <div className="db-kpi-main-amount-row">
                            <span className="db-kpi-amount">{formatCurrency(stats.revenue_this_month || 0)}</span>
                            {trendPct !== 0 && (
                                <span className={`db-kpi-tag ${isTrendUp ? 'up' : 'down'}`}>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                                        {isTrendUp ? (
                                            <path d="M12 19V5m0 0 4 4m-4-4-4 4" />
                                        ) : (
                                            <path d="M12 5v14m0 0 4-4m-4 4-4-4" />
                                        )}
                                    </svg>
                                    {Math.abs(trendPct)}%
                                </span>
                            )}
                        </div>
                        <div className="db-kpi-meta">
                            {stats.invoices_this_month || 0} comprobante
                            {(stats.invoices_this_month || 0) !== 1 ? 's' : ''} emitido
                            {(stats.invoices_this_month || 0) !== 1 ? 's' : ''} este mes
                        </div>

                        <BenchoTickWidget basePct={margenPct} caption="margen neto" />
                    </div>

                    {/* Monthly Invoiced Card */}
                    <div className="db-kpi">
                        <div className="db-kpi-top">
                            <span className="db-kpi-label">Facturado Este Mes</span>
                        </div>
                        <div className="db-kpi-amount">
                            {formatCurrency(stats.invoiced_amount_this_month || 0)}
                        </div>
                        <div className="db-kpi-compare">
                            {invPrev > 0 || invCur > 0 ? (
                                <span className={`trend-val ${isInvTrendUp ? 'up' : 'down'}`}>
                                    {isInvTrendUp ? '+' : ''}
                                    {invTrendPct}%
                                </span>
                            ) : (
                                <span className="trend-val">0%</span>
                            )}{' '}
                            vs mes anterior
                        </div>

                        <BenchoTickWidget basePct={facturadoPct} caption="meta" />
                    </div>

                    {/* Pending Collection Card */}
                    <div className="db-kpi">
                        <div className="db-kpi-top">
                            <span className="db-kpi-label">Pendiente de Cobro</span>
                        </div>
                        <div className="db-kpi-amount">
                            {formatCurrency(stats.pending_amount || 0)}
                        </div>
                        <div className="db-kpi-compare">
                            {Number(stats.overdue_count || 0) > 0 ? (
                                <span className="trend-val down">
                                    {stats.overdue_count} pendiente
                                    {stats.overdue_count > 1 ? 's' : ''}
                                </span>
                            ) : (
                                <span className="trend-val up">Al día</span>
                            )}{' '}
                            del total por cobrar
                        </div>

                        <BenchoTickWidget basePct={cobroPct} caption="tasa cobro" />
                    </div>
                </div>

                {/* ── ITBIS / Tax Segmented Equalizer Widget (1:1 Gridbase Bills) ── */}
                <TaxSegmentedEqualizer stats={stats} />

                {/* ── Two-Column Body (1:1 Gridbase Bills) ── */}
                <div className="db-body">
                    {/* LEFT: Actividad Reciente + Tasas de Cambio BPD */}
                    <div className="db-col">
                        <div className="db-card">
                            <div className="db-card-head">
                                <span className="db-card-title">Actividad Reciente</span>
                                <a href="/admin/pos" className="db-card-badge" style={{ textDecoration: 'none' }}>
                                    Ir a Caja
                                </a>
                            </div>
                            <div className="db-activity-head">
                                <span>Cliente / Estudiante</span>
                                <span>Fecha</span>
                                <span style={{ textAlign: 'right' }}>Monto</span>
                            </div>
                            <div>
                                {recent_invoices.length > 0 ? (
                                    recent_invoices.map((inv, idx) => {
                                        const avColor = avatarColors[idx % avatarColors.length];
                                        const name = inv.company_name || inv.contact_name || '?';
                                        const initial = name.charAt(0).toUpperCase();
                                        const isCN = !!inv.is_credit_note;
                                        const totalNum = parseFloat(inv.total || 0);
                                        const isZero = Math.abs(totalNum) < 0.001;
                                        const isPaid = inv.status === 'paid' || inv.status === 'accepted';

                                        let amountClass = 'db-amount-neg';
                                        let amountPrefix = '';
                                        if (isCN) {
                                            amountClass = 'db-amount-danger';
                                            amountPrefix = '-';
                                        } else if (isZero) {
                                            amountClass = 'db-amount-zero';
                                        } else if (isPaid) {
                                            amountClass = 'db-amount-pos';
                                            amountPrefix = '+';
                                        }

                                        const badgeClass = isCN
                                            ? 'badge-cancelled'
                                            : statusClasses[inv.status] || 'badge-draft';
                                        const badgeText = isCN
                                            ? 'Nota de Crédito'
                                            : statusLabels[inv.status] || inv.status;

                                        return (
                                            <div
                                                key={inv.id}
                                                onClick={() =>
                                                    window.open(
                                                        `/finance/ticket/${inv.id}`,
                                                        'Ticket',
                                                        'width=420,height=620'
                                                    )
                                                }
                                                className="db-activity-row"
                                                title="Click para ver comprobante / recibo"
                                            >
                                                <div className="db-activity-who">
                                                    <div className={`db-activity-avatar ${avColor}`}>
                                                        {initial}
                                                    </div>
                                                    <div style={{ minWidth: 0 }}>
                                                        <div className="db-activity-name">{name}</div>
                                                        <div
                                                            className="db-activity-sub"
                                                            style={{
                                                                display: 'flex',
                                                                alignItems: 'center',
                                                                gap: '6px',
                                                                flexWrap: 'wrap',
                                                            }}
                                                        >
                                                            <span>{inv.invoice_number}</span>
                                                            <span className={`bills-badge ${badgeClass}`}>
                                                                {badgeText}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="db-activity-date">
                                                    {inv.issue_date_formatted || inv.issue_date}
                                                </div>
                                                <div className={`db-activity-amount ${amountClass}`}>
                                                    {amountPrefix}
                                                    {formatCurrency(inv.total, inv.currency)}
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="db-empty">No hay actividad reciente</div>
                                )}
                            </div>
                        </div>

                        {/* BPD Exchange Rates Widget */}
                        <div className="db-card">
                            <div
                                className="db-card-head"
                                style={{
                                    borderBottom: '1px solid var(--color-border)',
                                    background: 'var(--bg-hover)',
                                }}
                            >
                                <span
                                    className="db-card-title"
                                    style={{ display: 'flex', alignItems: 'center', gap: '8px' }}
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        style={{
                                            width: '16px',
                                            height: '16px',
                                            color: 'var(--color-text-secondary)',
                                        }}
                                    >
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="16" />
                                        <line x1="8" y1="12" x2="16" y2="12" />
                                    </svg>
                                    Tasas de Cambio actualizadas
                                </span>
                                <span className="badge-active">En Vivo</span>
                            </div>
                            <div
                                style={{
                                    padding: '18px 24px',
                                    display: 'grid',
                                    gridTemplateColumns: '1fr 1fr',
                                    gap: '16px',
                                }}
                            >
                                <div
                                    style={{
                                        background: 'var(--bg-hover)',
                                        padding: '16px',
                                        borderRadius: 'var(--radius-md)',
                                        border: '1px solid var(--color-border)',
                                        textAlign: 'center',
                                    }}
                                >
                                    <div
                                        style={{
                                            fontSize: '11px',
                                            fontWeight: 600,
                                            color: 'var(--color-text-muted)',
                                            textTransform: 'uppercase',
                                            marginBottom: '4px',
                                            letterSpacing: '0.04em',
                                        }}
                                    >
                                        Dólar (USD)
                                    </div>
                                    <div
                                        style={{
                                            fontSize: '22px',
                                            fontWeight: 800,
                                            color: 'var(--color-text-primary)',
                                            letterSpacing: '-0.02em',
                                        }}
                                    >
                                        RD$ {Number(exchange_rates?.USD || 60.35).toFixed(2)}
                                    </div>
                                    <div
                                        style={{
                                            fontSize: '10px',
                                            color: 'var(--color-text-muted)',
                                            marginTop: '2px',
                                        }}
                                    >
                                        Tasa de Venta BPD
                                    </div>
                                </div>
                                <div
                                    style={{
                                        background: 'var(--bg-hover)',
                                        padding: '16px',
                                        borderRadius: 'var(--radius-md)',
                                        border: '1px solid var(--color-border)',
                                        textAlign: 'center',
                                    }}
                                >
                                    <div
                                        style={{
                                            fontSize: '11px',
                                            fontWeight: 600,
                                            color: 'var(--color-text-muted)',
                                            textTransform: 'uppercase',
                                            marginBottom: '4px',
                                            letterSpacing: '0.04em',
                                        }}
                                    >
                                        Euro (EUR)
                                    </div>
                                    <div
                                        style={{
                                            fontSize: '22px',
                                            fontWeight: 800,
                                            color: 'var(--color-text-primary)',
                                            letterSpacing: '-0.02em',
                                        }}
                                    >
                                        RD$ {Number(exchange_rates?.EUR || 65.9).toFixed(2)}
                                    </div>
                                    <div
                                        style={{
                                            fontSize: '10px',
                                            color: 'var(--color-text-muted)',
                                            marginTop: '2px',
                                        }}
                                    >
                                        Tasa de Venta BPD
                                    </div>
                                </div>
                            </div>
                            <div
                                style={{
                                    padding: '12px 24px',
                                    background: 'var(--bg-hover)',
                                    borderTop: '1px solid var(--color-border)',
                                    fontSize: '11px',
                                    color: 'var(--color-text-muted)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: '6px',
                                }}
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    style={{ width: '12px', height: '12px', flexShrink: 0 }}
                                >
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                Sincronizado vía BPD el {exchange_rates?.updated_at || dateStr}
                            </div>
                        </div>
                    </div>

                    {/* RIGHT: Cashflow UIBarChart + Overdue Invoices */}
                    <div className="db-col">
                        <div className="db-card">
                            <div
                                className="db-card-head"
                                style={{
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center',
                                    flexWrap: 'wrap',
                                    gap: '10px',
                                }}
                            >
                                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                    <span className="db-card-title">Flujo de Caja</span>
                                    <span className="db-card-badge">Últimos 12 meses</span>
                                </div>
                                <div className="segmented-control">
                                    {[
                                        { key: 'net', label: 'Flujo Neto' },
                                        { key: 'revenue', label: 'Ingresos' },
                                        { key: 'expense', label: 'Gastos' },
                                    ].map((tab) => (
                                        <button
                                            key={tab.key}
                                            type="button"
                                            onClick={() => setActiveSeries(tab.key)}
                                            className={`segment-item ${
                                                activeSeries === tab.key ? 'active' : ''
                                            }`}
                                        >
                                            {tab.label}
                                        </button>
                                    ))}
                                </div>
                            </div>
                            <BillsUIBarChart monthlyData={monthly_data} series={activeSeries} />
                        </div>

                        {/* Facturas Vencidas / Cuentas por Cobrar */}
                        <div className="db-card">
                            <div className="db-card-head">
                                <span className="db-card-title">
                                    {overdue_invoices.length > 0 && <span className="db-pulse" />}
                                    Facturas / Cobros Pendientes
                                </span>
                                <span className="bills-badge badge-overdue">
                                    {overdue_invoices.length}
                                </span>
                            </div>

                            {overdue_invoices.length > 0 ? (
                                overdue_invoices.length >= 2 ? (
                                    <>
                                        <div className="db-overdue-grid">
                                            {overdue_invoices.slice(0, 2).map((o, i) => (
                                                <a
                                                    key={o.id}
                                                    href="/admin/pos"
                                                    className="db-overdue-cell"
                                                >
                                                    <div className="db-overdue-cell-title">
                                                        {o.invoice_number}
                                                    </div>
                                                    <div className="db-overdue-cell-sub">
                                                        {o.company_name} — {o.contact_name}
                                                    </div>
                                                    <div className="db-overdue-cell-amount">
                                                        {formatCurrency(o.total, o.currency)}
                                                    </div>
                                                    <div className="db-overdue-bar-wrap">
                                                        <div
                                                            className="db-overdue-bar"
                                                            style={{
                                                                width: `${i === 0 ? 78 : 56}%`,
                                                            }}
                                                        />
                                                    </div>
                                                </a>
                                            ))}
                                        </div>
                                        {overdue_invoices.slice(2).map((o) => (
                                            <a
                                                key={o.id}
                                                href="/admin/pos"
                                                className="db-overdue-item"
                                            >
                                                <div>
                                                    <div
                                                        style={{
                                                            fontWeight: 600,
                                                            fontSize: '13px',
                                                            color: 'var(--color-text-primary)',
                                                        }}
                                                    >
                                                        {o.invoice_number}
                                                    </div>
                                                    <div
                                                        style={{
                                                            fontSize: '12px',
                                                            color: 'var(--color-text-muted)',
                                                            marginTop: '1px',
                                                        }}
                                                    >
                                                        {o.company_name} — {o.contact_name}
                                                    </div>
                                                </div>
                                                <div style={{ textAlign: 'right' }}>
                                                    <div
                                                        style={{
                                                            fontWeight: 700,
                                                            fontSize: '14px',
                                                            color: 'var(--color-danger-icon)',
                                                        }}
                                                    >
                                                        {formatCurrency(o.total, o.currency)}
                                                    </div>
                                                    <div
                                                        style={{
                                                            fontSize: '11px',
                                                            color: 'var(--color-text-muted)',
                                                            marginTop: '1px',
                                                        }}
                                                    >
                                                        {o.due_date}
                                                    </div>
                                                </div>
                                            </a>
                                        ))}
                                    </>
                                ) : (
                                    overdue_invoices.map((o) => (
                                        <a key={o.id} href="/admin/pos" className="db-overdue-item">
                                            <div>
                                                <div
                                                    style={{
                                                        fontWeight: 700,
                                                        fontSize: '14px',
                                                        color: 'var(--color-text-primary)',
                                                    }}
                                                >
                                                    {o.invoice_number}
                                                </div>
                                                <div
                                                    style={{
                                                        fontSize: '12px',
                                                        color: 'var(--color-text-muted)',
                                                        marginTop: '1px',
                                                    }}
                                                >
                                                    {o.company_name} — {o.contact_name}
                                                </div>
                                            </div>
                                            <div
                                                style={{
                                                    fontWeight: 800,
                                                    fontSize: '15px',
                                                    color: 'var(--color-danger-icon)',
                                                }}
                                            >
                                                {formatCurrency(o.total, o.currency)}
                                            </div>
                                        </a>
                                    ))
                                )
                            ) : (
                                <div className="db-empty">No hay cobros vencidos pendientes</div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>
        </AuthenticatedLayout>
    );
}
