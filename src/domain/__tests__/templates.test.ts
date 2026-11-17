import { describe, expect, it } from '@jest/globals';

import { TemplateColors } from '@/constants/theme';

import { searchTemplates, TEMPLATES } from '../templates';

describe('Spielvorlagen', () => {
  it('haben eindeutige IDs und eine Farbe', () => {
    const ids = TEMPLATES.map((t) => t.id);
    expect(new Set(ids).size).toBe(ids.length);
    for (const id of ids) expect(TemplateColors[id]).toBeDefined();
  });

  it('haben eine Kurzanleitung und, wo vorhanden, einen https-Link', () => {
    for (const t of TEMPLATES) {
      expect(t.guide.length).toBeGreaterThanOrEqual(3);
      for (const line of t.guide) expect(line.trim().length).toBeGreaterThan(10);
      if (t.rules) {
        expect(t.rules.url).toMatch(/^https:\/\//);
        expect(t.rules.url).not.toMatch(/\s/);
        expect(t.rules.source.length).toBeGreaterThan(0);
      }
    }
  });

  it('findet die neuen Kartenspiele über Namen und Varianten', () => {
    expect(searchTemplates('tschau').map((t) => t.id)).toEqual(['tschau-sepp']);
    expect(searchTemplates('mau mau').map((t) => t.id)).toEqual(['tschau-sepp']);
    expect(searchTemplates('gemsch').map((t) => t.id)).toEqual(['gaemsch']);
    expect(searchTemplates('arschloch').map((t) => t.id)).toEqual(['arschloechle']);
    expect(searchTemplates('31').map((t) => t.id)).toEqual(['schwimmen']);
    expect(searchTemplates('romme').map((t) => t.id)).toEqual(['romme']);
  });

  it('ordnet die neuen Spiele den Kartenspielen zu', () => {
    const karten = searchTemplates('', 'Kartenspiel').map((t) => t.id);
    expect(karten).toEqual(expect.arrayContaining(['tschau-sepp', 'arschloechle', 'gaemsch', 'schwimmen', 'romme', 'stiche-ansagen']));
  });
});
