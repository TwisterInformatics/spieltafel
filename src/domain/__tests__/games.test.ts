import { describe, expect, it } from '@jest/globals';

import { isFinishedByRules, placementPoints, ranking, scoreTable, totals, wizardPoints } from '../scoring';
import { getTemplate, searchTemplates, TEMPLATES } from '../templates';
import type { Game, Participant } from '../types';

const p = (id: string): Participant => ({ id, name: id, color: '#000' });

function game(partial: Partial<Game>): Game {
  return {
    id: 'g',
    templateId: 'x',
    createdAt: 0,
    finishedAt: null,
    participants: [p('a'), p('b'), p('c')],
    settings: { winner: 'highest', end: { type: 'manual' } },
    rounds: [],
    ...partial,
  };
}

describe('Kurzanleitungen', () => {
  it('hat jedes Spiel mit Ziel, Ablauf und Wertung', () => {
    for (const t of TEMPLATES) {
      expect(t.guide.goal.length).toBeGreaterThan(10);
      expect(t.guide.steps.length).toBeGreaterThan(0);
      expect(t.guide.scoring.length).toBeGreaterThan(0);
    }
  });
  it('verlinkt nur auf https-Seiten', () => {
    for (const t of TEMPLATES) if (t.guide.link) expect(t.guide.link.url).toMatch(/^https:\/\//);
  });
  it('hat eindeutige IDs', () => {
    expect(new Set(TEMPLATES.map((t) => t.id)).size).toBe(TEMPLATES.length);
  });
  it('findet die neuen Spiele auch ohne Umlaute', () => {
    expect(searchTemplates('gamsch').map((t) => t.id)).toEqual(['gaemsch']);
    expect(searchTemplates('arschloch').map((t) => t.id)).toEqual(['arschloechle']);
    expect(searchTemplates('mäxchen').map((t) => t.id)).toEqual(['maexle']);
    expect(searchTemplates('schwimmen').map((t) => t.id)).toEqual(['schnauz']);
    expect(searchTemplates('', 'Würfelspiel').map((t) => t.id)).toEqual(['maexle']);
  });
});

describe('Arschlöchle (Platzierung)', () => {
  it('gibt dem Ersten Spieler minus 1 Punkte und dem Letzten 0', () => {
    expect(placementPoints(['b', 'c', 'a'], ['a', 'b', 'c'])).toEqual({ b: 2, c: 1, a: 0 });
  });
  it('setzt den Letzten automatisch ans Ende', () => {
    expect(placementPoints(['c', 'a'], ['a', 'b', 'c'])).toEqual({ c: 2, a: 1, b: 0 });
  });
  it('zählt über mehrere Runden', () => {
    const g = game({
      rounds: [
        { kind: 'placement', order: ['a', 'b'] },
        { kind: 'placement', order: ['a', 'c'] },
      ],
    });
    expect(totals(g)).toEqual({ a: 4, b: 1, c: 1 });
    expect(ranking(g)[0].participant.id).toBe('a');
  });
});

describe('Leben (Schnauz, Mäxle)', () => {
  const settings = { winner: 'highest' as const, end: { type: 'lastStanding' as const, lives: 2 } };
  it('startet mit den Leben und zieht pro Verlust eins ab', () => {
    const g = game({ settings, rounds: [{ kind: 'lives', losers: ['a'] }] });
    expect(totals(g)).toEqual({ a: 1, b: 2, c: 2 });
    expect(scoreTable(g)).toEqual([{ a: -1, b: 0, c: 0 }]);
  });
  it('endet, wenn nur noch einer Leben hat', () => {
    const rounds = [
      { kind: 'lives' as const, losers: ['a', 'b'] },
      { kind: 'lives' as const, losers: ['a'] },
    ];
    expect(isFinishedByRules(game({ settings, rounds }))).toBe(false);
    const done = game({ settings, rounds: [...rounds, { kind: 'lives', losers: ['b'] }] });
    expect(isFinishedByRules(done)).toBe(true);
    expect(ranking(done)[0].participant.id).toBe('c');
  });
});

describe('Wizard', () => {
  it('gibt 20 plus 10 je Stich bei getroffener Ansage', () => {
    expect(wizardPoints(0, 0)).toBe(20);
    expect(wizardPoints(3, 3)).toBe(50);
  });
  it('zieht 10 je Stich Abweichung ab', () => {
    expect(wizardPoints(2, 0)).toBe(-20);
    expect(wizardPoints(1, 4)).toBe(-30);
  });
  it('rechnet die Runden aus den 60 Karten', () => {
    expect(getTemplate('wizard')?.roundsFromDeck).toBe(60);
  });
});

describe('Strafpunkte-Spiele', () => {
  it('beendet Tschau Sepp bei 200 und der Tiefste gewinnt', () => {
    const t = getTemplate('tschau-sepp')!;
    const g = game({
      settings: { winner: t.winner, end: t.end },
      rounds: [{ kind: 'rounds', scores: { a: 120, b: 30, c: 90 } }, { kind: 'rounds', scores: { a: 85, b: 10, c: 0 } }],
    });
    expect(isFinishedByRules(g)).toBe(true);
    expect(ranking(g)[0].participant.id).toBe('b');
  });
});
