import { describe, expect, it } from '@jest/globals';

import { COIFFEUR_MODES, searchTemplates } from '../templates';
import {
  isFinishedByRules,
  jassCounterPoints,
  openCoiffeurModes,
  ranking,
  scoreTable,
  skyjoRoundScores,
  totals,
} from '../scoring';
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

describe('Skyjo', () => {
  it('verdoppelt die Punkte des Beenders, wenn jemand gleich tief oder tiefer liegt', () => {
    expect(skyjoRoundScores({ a: 7, b: 12, c: -2 }, 'b')).toEqual({ a: 7, b: 24, c: -2 });
    expect(skyjoRoundScores({ a: 7, b: 7 }, 'a')).toEqual({ a: 14, b: 7 });
  });
  it('verdoppelt nicht, wenn der Beender allein am tiefsten liegt', () => {
    expect(skyjoRoundScores({ a: 3, b: 12 }, 'a')).toEqual({ a: 3, b: 12 });
  });
  it('verdoppelt keine negativen oder null Punkte', () => {
    expect(skyjoRoundScores({ a: -1, b: -5 }, 'a')).toEqual({ a: -1, b: -5 });
  });
  it('endet bei 100 Punkten, tiefste Summe gewinnt', () => {
    const g = game({
      participants: [p('a'), p('b')],
      settings: { winner: 'lowest', end: { type: 'target', target: 100 } },
      rounds: [
        { kind: 'skyjo', scores: { a: 40, b: 10 }, finisherId: 'b' },
        { kind: 'skyjo', scores: { a: 30, b: 20 }, finisherId: 'a' },
      ],
    });
    // Runde 2: a beendet mit 30, b hat 20 -> a bekommt 60
    expect(totals(g)).toEqual({ a: 100, b: 30 });
    expect(isFinishedByRules(g)).toBe(true);
    expect(ranking(g)[0].participant.id).toBe('b');
  });
});

describe('Jass Schieber', () => {
  it('rechnet Gegenpunkte und Match', () => {
    expect(jassCounterPoints(98)).toBe(59);
    expect(jassCounterPoints(157)).toBe(0);
    expect(jassCounterPoints(257)).toBe(0);
    expect(jassCounterPoints(0)).toBe(157);
  });
  it('multipliziert Stich, Weis und Stöck mit dem Trumpf-Faktor', () => {
    const g = game({
      participants: [p('a'), p('b')],
      settings: { winner: 'highest', end: { type: 'target', target: 1000 } },
      rounds: [
        { kind: 'jassSchieber', factor: 2, modeName: 'Rosen', stich: { a: 98, b: 59 }, weis: { a: 20, b: 0 }, stoeck: { a: 0, b: 20 } },
      ],
    });
    expect(totals(g)).toEqual({ a: 236, b: 158 });
    expect(isFinishedByRules(g)).toBe(false);
  });
});

describe('Differenzler', () => {
  it('zählt den Abstand zwischen Ansage und erzielten Punkten', () => {
    const g = game({
      participants: [p('a'), p('b')],
      settings: { winner: 'lowest', end: { type: 'rounds', rounds: 2 } },
      rounds: [
        { kind: 'differenzler', ansage: { a: 40, b: 90 }, erzielt: { a: 50, b: 107 } },
        { kind: 'differenzler', ansage: { a: 60, b: 70 }, erzielt: { a: 60, b: 97 } },
      ],
    });
    expect(totals(g)).toEqual({ a: 10, b: 44 });
    expect(isFinishedByRules(g)).toBe(true);
    expect(ranking(g).map((r) => r.participant.id)).toEqual(['a', 'b']);
  });
});

describe('Coiffeur', () => {
  it('multipliziert mit dem Faktor der Spielart und endet, wenn alles gespielt ist', () => {
    const modes = COIFFEUR_MODES.slice(0, 2);
    const g = game({
      participants: [p('a'), p('b')],
      settings: { winner: 'highest', end: { type: 'allModes' }, modes },
      rounds: [
        { kind: 'coiffeur', teamId: 'a', modeName: modes[0].name, points: 100 },
        { kind: 'coiffeur', teamId: 'a', modeName: modes[1].name, points: 60 },
        { kind: 'coiffeur', teamId: 'b', modeName: modes[1].name, points: 80 },
      ],
    });
    expect(scoreTable(g)[1]).toEqual({ a: 60 * modes[1].factor, b: 0 });
    expect(openCoiffeurModes(g, 'b')).toEqual([modes[0].name]);
    expect(isFinishedByRules(g)).toBe(false);
    g.rounds.push({ kind: 'coiffeur', teamId: 'b', modeName: modes[0].name, points: 10 });
    expect(isFinishedByRules(g)).toBe(true);
  });
});

describe('Freie Zählung', () => {
  it('summiert frei und endet nie von selbst', () => {
    const g = game({
      rounds: [
        { kind: 'rounds', scores: { a: 5, b: -3, c: 0 } },
        { kind: 'rounds', scores: { a: 1, b: 10 } },
      ],
    });
    expect(totals(g)).toEqual({ a: 6, b: 7, c: 0 });
    expect(isFinishedByRules(g)).toBe(false);
  });
  it('teilt Ränge bei Gleichstand', () => {
    const g = game({ rounds: [{ kind: 'rounds', scores: { a: 5, b: 5, c: 1 } }] });
    expect(ranking(g).map((r) => r.rank)).toEqual([1, 1, 3]);
  });
});

describe('Suche', () => {
  it('findet Spiele über Name und Suchbegriffe, ohne Gross- und Kleinschreibung', () => {
    expect(searchTemplates('jass schieber').map((t) => t.id)).toEqual(['jass-schieber']);
    expect(searchTemplates('jass').map((t) => t.id)).toEqual(
      expect.arrayContaining(['jass-schieber', 'jass-differenzler', 'jass-coiffeur']),
    );
    expect(searchTemplates('QWIRKLE').map((t) => t.id)).toEqual(['qwirkle']);
    expect(searchTemplates('zahlen').map((t) => t.id)).toEqual(['frei']);
    expect(searchTemplates('').length).toBe(15);
    expect(searchTemplates('', 'Legespiel').map((t) => t.id)).toEqual(['qwirkle']);
  });
});
