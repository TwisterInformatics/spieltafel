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

  it('verlinken nur deutschsprachige Anleitungen', () => {
    for (const t of TEMPLATES) {
      if (!t.rules) continue;
      // Wikipedia nur in Deutsch, Pagat nur die deutsche Übersetzung unter /de/
      expect(t.rules.url).not.toMatch(/\/\/(?!de\.)[a-z-]+\.wikipedia\.org/);
      expect(t.rules.url).not.toMatch(/pagat\.com\/(?!de\/)/);
      expect(t.rules.source).not.toMatch(/englisch/i);
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

  it('enthält Uno, Mäxle, Phase 10, Wizard, Skip-Bo und Flip 7', () => {
    expect(searchTemplates('uno').map((t) => t.id)).toEqual(['uno']);
    expect(searchTemplates('mäxchen').map((t) => t.id)).toEqual(['maexle']);
    expect(searchTemplates('meiern').map((t) => t.id)).toEqual(['maexle']);
    expect(searchTemplates('phase 10').map((t) => t.id)).toEqual(['phase-10']);
    expect(searchTemplates('wizard').map((t) => t.id)).toEqual(['wizard']);
    expect(searchTemplates('skipbo').map((t) => t.id)).toEqual(['skip-bo']);
    expect(searchTemplates('flip 7').map((t) => t.id)).toEqual(['flip-7']);
    expect(searchTemplates('', 'Würfelspiel').map((t) => t.id)).toEqual(['maexle']);
  });
});
