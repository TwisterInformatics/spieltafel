import { describe, expect, it } from '@jest/globals';

import { layoutFor, scaleStyles } from '../layout';

describe('Tablet-Layout', () => {
  it('erkennt Tablets an der kürzeren Bildschirmseite', () => {
    expect(layoutFor(390, 844).isTablet).toBe(false);
    expect(layoutFor(844, 390).isTablet).toBe(false);
    expect(layoutFor(820, 1180).isTablet).toBe(true);
    expect(layoutFor(1366, 1024).isTablet).toBe(true);
  });
  it('vergrössert Schrift und Abstände, aber keine Prozentwerte oder Farben', () => {
    const s = scaleStyles({ a: { fontSize: 10, padding: 20, width: '100%', color: '#fff', flex: 1 } }, 1.3);
    expect(s.a).toEqual({ fontSize: 13, padding: 26, width: '100%', color: '#fff', flex: 1 });
  });
  it('lässt Handy-Stile unverändert', () => {
    const base = { a: { fontSize: 10 } };
    expect(scaleStyles(base, 1)).toBe(base);
  });
});
