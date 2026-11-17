import { useMemo } from 'react';
import { useWindowDimensions } from 'react-native';

/** Ab dieser kürzeren Bildschirmseite (in dp) gilt ein Gerät als Tablet. */
const TABLET_MIN_SIDE = 600;
/** Wie stark Schrift, Abstände und Bedienelemente auf Tablets wachsen. */
const TABLET_SCALE = 1.3;
/** Maximale Breite des Inhalts auf Tablets, damit Zeilen nicht über den ganzen Bildschirm laufen. */
const TABLET_MAX_WIDTH = 860;
/** Ab dieser Breite (in dp) zeigen Tablets zwei Spalten nebeneinander, typisch im Querformat. */
const WIDE_MIN_WIDTH = 960;

export interface Layout {
  isTablet: boolean;
  /** Tablet mit genug Breite für zwei Spalten (Querformat). */
  wide: boolean;
  /** Spalten für Kachelraster: 1 auf Handys, 2 auf Tablets hochkant, 3 auf breiten Tablets. */
  gridColumns: number;
  /** Faktor für Grössen: 1 auf Handys, grösser auf Tablets. */
  k: number;
  /** Maximale Inhaltsbreite oder undefined auf Handys. */
  maxWidth: number | undefined;
}

export function layoutFor(width: number, height: number): Layout {
  const isTablet = Math.min(width, height) >= TABLET_MIN_SIDE;
  const wide = isTablet && width >= WIDE_MIN_WIDTH;
  return {
    isTablet,
    wide,
    gridColumns: wide ? 3 : isTablet ? 2 : 1,
    k: isTablet ? TABLET_SCALE : 1,
    maxWidth: isTablet ? TABLET_MAX_WIDTH : undefined,
  };
}

export function useLayout(): Layout {
  const { width, height } = useWindowDimensions();
  return useMemo(() => layoutFor(width, height), [width, height]);
}

/**
 * Die Tastatur kommt in Bildschirm-Koordinaten, der Container wird aber relativ zu seinem Screen gemessen.
 * In einem Modal-Blatt (Runde erfassen) fehlt darum der Abstand des Blatts zum oberen Bildschirmrand.
 * Auf dem iPhone reicht das Blatt bis ganz unten, der fehlende Abstand ist also die ganze Differenz.
 * Auf dem iPad schwebt das Blatt etwa mittig, dort ist es die halbe Differenz.
 */
export function keyboardOverlap(bottom: number, windowHeight: number, keyboardTop: number, isTablet: boolean): number {
  const missing = Math.max(0, windowHeight - bottom);
  const sheetTop = isTablet ? missing / 2 : missing;
  return Math.max(0, Math.round(bottom + sheetTop - keyboardTop));
}

const SCALED_KEYS = new Set([
  'fontSize',
  'lineHeight',
  'letterSpacing',
  'width',
  'height',
  'minWidth',
  'minHeight',
  'gap',
  'rowGap',
  'columnGap',
  'padding',
  'paddingTop',
  'paddingBottom',
  'paddingLeft',
  'paddingRight',
  'paddingHorizontal',
  'paddingVertical',
  'margin',
  'marginTop',
  'marginBottom',
  'marginLeft',
  'marginRight',
  'marginHorizontal',
  'marginVertical',
  'borderRadius',
  'borderWidth',
]);

/** Multipliziert alle Grössenangaben eines Stylesheets mit k. Prozentwerte bleiben unverändert. */
export function scaleStyles<T extends Record<string, object>>(styles: T, k: number): T {
  if (k === 1) return styles;
  const out: Record<string, object> = {};
  for (const [name, style] of Object.entries(styles)) {
    const scaled: Record<string, unknown> = {};
    for (const [key, value] of Object.entries(style)) {
      scaled[key] = SCALED_KEYS.has(key) && typeof value === 'number' ? Math.round(value * k * 10) / 10 : value;
    }
    out[name] = scaled;
  }
  return out as T;
}

export function useScaledStyles<T extends Record<string, object>>(styles: T): T {
  const { k } = useLayout();
  return useMemo(() => scaleStyles(styles, k), [styles, k]);
}
