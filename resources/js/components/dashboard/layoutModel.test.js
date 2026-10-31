import { describe, expect, it } from 'vitest';
import {
    addWidget,
    isSameLayout,
    keysMissingData,
    moveWidget,
    removeWidget,
    resizeWidget,
} from './layoutModel';

const catalog = {
    kpis: { key: 'kpis', sizes: ['lg', 'xl'], defaultSize: 'xl' },
    cash: { key: 'cash', sizes: ['sm', 'md'], defaultSize: 'sm' },
    audit: { key: 'audit', sizes: ['sm', 'md'], defaultSize: 'sm' },
};

const layout = [
    { key: 'kpis', size: 'xl' },
    { key: 'cash', size: 'sm' },
    { key: 'audit', size: 'md' },
];

describe('moveWidget', () => {
    it('moves the active widget to the position of the target', () => {
        expect(moveWidget(layout, 'audit', 'kpis').map((w) => w.key)).toEqual(['audit', 'kpis', 'cash']);
        expect(moveWidget(layout, 'kpis', 'audit').map((w) => w.key)).toEqual(['cash', 'audit', 'kpis']);
    });

    it('returns the same array when nothing changes or keys are unknown', () => {
        expect(moveWidget(layout, 'cash', 'cash')).toBe(layout);
        expect(moveWidget(layout, 'nope', 'cash')).toBe(layout);
        expect(moveWidget(layout, 'cash', null)).toBe(layout);
    });

    it('does not mutate the input', () => {
        const copy = structuredClone(layout);
        moveWidget(layout, 'audit', 'kpis');
        expect(layout).toEqual(copy);
    });
});

describe('resizeWidget', () => {
    it('changes the size when the widget allows it', () => {
        expect(resizeWidget(layout, 'cash', 'md', catalog)[1]).toEqual({ key: 'cash', size: 'md' });
    });

    it('ignores sizes the widget does not allow', () => {
        expect(resizeWidget(layout, 'kpis', 'sm', catalog)).toBe(layout);
        expect(resizeWidget(layout, 'ghost', 'sm', catalog)).toBe(layout);
    });
});

describe('addWidget / removeWidget', () => {
    it('appends a widget with its default size', () => {
        const next = addWidget([{ key: 'kpis', size: 'xl' }], catalog.cash);
        expect(next).toEqual([{ key: 'kpis', size: 'xl' }, { key: 'cash', size: 'sm' }]);
    });

    it('does not add duplicates', () => {
        expect(addWidget(layout, catalog.cash)).toBe(layout);
    });

    it('removes a widget by key', () => {
        expect(removeWidget(layout, 'cash').map((w) => w.key)).toEqual(['kpis', 'audit']);
        expect(removeWidget(layout, 'ghost')).toBe(layout);
    });
});

describe('isSameLayout', () => {
    it('compares order and sizes', () => {
        expect(isSameLayout(layout, structuredClone(layout))).toBe(true);
        expect(isSameLayout(layout, moveWidget(layout, 'audit', 'kpis'))).toBe(false);
        expect(isSameLayout(layout, resizeWidget(layout, 'cash', 'md', catalog))).toBe(false);
        expect(isSameLayout([], [])).toBe(true);
    });
});

describe('keysMissingData', () => {
    it('lists widgets in the layout whose data prop has not been loaded', () => {
        const props = { widget_kpis: { items: [] }, widget_cash: undefined };
        expect(keysMissingData(layout, props)).toEqual(['cash', 'audit']);
    });
});
