import { describe, expect, it } from '@jest/globals';

import { formatNumberInput, parseNumberInput, toggleSign } from '../numberInput';

describe('parseNumberInput', () => {
  it('lässt ein einzelnes Minus stehen, damit man negative Zahlen eintippen kann', () => {
    expect(parseNumberInput('-')).toEqual({ text: '-', value: null });
    expect(parseNumberInput('-1')).toEqual({ text: '-1', value: -1 });
    expect(parseNumberInput('-12')).toEqual({ text: '-12', value: -12 });
  });

  it('liest positive Zahlen und leere Felder', () => {
    expect(parseNumberInput('157')).toEqual({ text: '157', value: 157 });
    expect(parseNumberInput('')).toEqual({ text: '', value: null });
  });

  it('entfernt Komma, Punkt, Leerzeichen und Minus mitten in der Zahl', () => {
    expect(parseNumberInput('1.5')).toEqual({ text: '15', value: 15 });
    expect(parseNumberInput('1,2 3')).toEqual({ text: '123', value: 123 });
    expect(parseNumberInput('5-')).toEqual({ text: '5', value: 5 });
    expect(parseNumberInput('--4')).toEqual({ text: '-4', value: -4 });
  });

  it('formatiert gespeicherte Werte für das Feld', () => {
    expect(formatNumberInput(null)).toBe('');
    expect(formatNumberInput(-7)).toBe('-7');
  });
});

describe('toggleSign', () => {
  it('dreht das Vorzeichen um', () => {
    expect(toggleSign('5')).toEqual({ text: '-5', value: -5 });
    expect(toggleSign('-12')).toEqual({ text: '12', value: 12 });
  });

  it('setzt im leeren Feld ein Minus, damit die Ziffern danach negativ werden', () => {
    expect(toggleSign('')).toEqual({ text: '-', value: null });
    expect(toggleSign('-')).toEqual({ text: '', value: null });
  });
});
