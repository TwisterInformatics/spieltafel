import { describe, expect, it } from '@jest/globals';

import { keyboardOverlap, layoutFor, scaleStyles } from '../layout';

describe('Tablet-Layout', () => {
  it('erkennt Tablets an der kürzeren Bildschirmseite', () => {
    expect(layoutFor(390, 844).isTablet).toBe(false);
    expect(layoutFor(844, 390).isTablet).toBe(false);
    expect(layoutFor(820, 1180).isTablet).toBe(true);
    expect(layoutFor(1366, 1024).isTablet).toBe(true);
  });
  it('zeigt zwei Spalten nur auf Tablets im Querformat', () => {
    expect(layoutFor(1280, 800)).toMatchObject({ wide: true, gridColumns: 3 });
    expect(layoutFor(800, 1280)).toMatchObject({ wide: false, gridColumns: 2 });
    expect(layoutFor(390, 844)).toMatchObject({ wide: false, gridColumns: 1 });
    expect(layoutFor(915, 412)).toMatchObject({ wide: false, gridColumns: 1 });
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

describe('Tastatur auf iOS', () => {
  it('rechnet auf normalen Screens die Überdeckung bis zum unteren Bildschirmrand', () => {
    expect(keyboardOverlap(844, 844, 508, false)).toBe(336);
    expect(keyboardOverlap(1376, 1376, 971, true)).toBe(405);
  });
  it('gleicht in Modal-Blättern den Abstand zum oberen Bildschirmrand aus', () => {
    // iPhone: Blatt beginnt 54 unter dem Rand und reicht bis ganz unten.
    expect(keyboardOverlap(790, 844, 508, false)).toBe(336);
    // iPad: Blatt schwebt, oben 43 und unten 52 Abstand.
    expect(keyboardOverlap(1281, 1376, 971, true)).toBeGreaterThanOrEqual(350);
  });
  it('liefert 0, wenn die Tastatur am unteren Rand ausserhalb des Bildschirms liegt', () => {
    expect(keyboardOverlap(844, 844, 844, false)).toBe(0);
  });
});
