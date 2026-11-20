/**
 * Bereinigt die Eingabe eines Zahlenfelds: nur Ziffern und ein Minus ganz vorne.
 * `text` ist, was im Feld stehen bleibt (auch ein einzelnes "-" während der Eingabe),
 * `value` die Zahl dazu oder null, solange noch keine Ziffer da ist.
 */
export function parseNumberInput(input: string): { text: string; value: number | null } {
  const negative = input.trimStart().startsWith('-');
  const digits = input.replace(/[^0-9]/g, '');
  const text = (negative ? '-' : '') + digits;
  if (digits === '') return { text, value: null };
  const n = parseInt(text, 10);
  return { text, value: Number.isNaN(n) ? null : n };
}

/** Dreht das Vorzeichen um (für die ±-Taste, weil manche Tastaturen kein Minus haben). */
export function toggleSign(text: string): { text: string; value: number | null } {
  const current = parseNumberInput(text).text;
  return parseNumberInput(current.startsWith('-') ? current.slice(1) : '-' + current);
}

export function formatNumberInput(value: number | null): string {
  return value === null ? '' : String(value);
}
