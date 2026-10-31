import { describe, expect, it } from 'vitest';
import { luminance, visibleOn } from './color';

describe('luminance', () => {
    it('returns 0 for black and 1 for white', () => {
        expect(luminance('#000000')).toBeCloseTo(0);
        expect(luminance('#ffffff')).toBeCloseTo(1);
    });

    it('accepts 3-digit hex and returns null for invalid input', () => {
        expect(luminance('#fff')).toBeCloseTo(1);
        expect(luminance('not-a-color')).toBeNull();
        expect(luminance(undefined)).toBeNull();
    });
});

describe('visibleOn', () => {
    it('keeps colors that already contrast with the background', () => {
        expect(visibleOn('#1c1917', false)).toBe('#1c1917');
        expect(visibleOn('#bb3902', true)).toBe('#bb3902');
    });

    it('swaps near-black colors for a light fallback in dark mode', () => {
        expect(visibleOn('#1c1917', true)).toBe('#e7e5e4');
        expect(visibleOn('#000', true, '#ccc')).toBe('#ccc');
    });

    it('swaps near-white colors for a dark fallback in light mode', () => {
        expect(visibleOn('#fafafa', false)).toBe('#1c1917');
    });

    it('returns invalid colors untouched', () => {
        expect(visibleOn('rgb(0,0,0)', true)).toBe('rgb(0,0,0)');
    });
});
