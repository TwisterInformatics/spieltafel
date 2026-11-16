import type { GameTemplate, JassMode } from './types';

/** Trumpf-Faktoren beim Schieber. Viele Runden spielen eigene Hausregeln, deshalb nur Vorgabe. */
export const SCHIEBER_MODES: JassMode[] = [
  { name: 'Eicheln', factor: 1 },
  { name: 'Schellen', factor: 1 },
  { name: 'Rosen', factor: 2 },
  { name: 'Schilten', factor: 2 },
  { name: 'Obenabe', factor: 3 },
  { name: 'Undenufe', factor: 3 },
];

/** Spielarten beim Coiffeur. Jede Spielart spielt jedes Team einmal. Faktoren als Vorgabe. */
export const COIFFEUR_MODES: JassMode[] = [
  { name: 'Eicheln', factor: 1 },
  { name: 'Schellen', factor: 2 },
  { name: 'Rosen', factor: 3 },
  { name: 'Schilten', factor: 4 },
  { name: 'Obenabe', factor: 5 },
  { name: 'Undenufe', factor: 6 },
];

export const TEMPLATES: GameTemplate[] = [
  {
    id: 'skyjo',
    name: 'Skyjo',
    category: 'Kartenspiel',
    description: '2 bis 8 Spieler · tiefste Summe gewinnt',
    keywords: ['skyjo', 'karten', 'magilano'],
    kind: 'skyjo',
    minPlayers: 2,
    maxPlayers: 8,
    winner: 'lowest',
    end: { type: 'target', target: 100 },
  },
  {
    id: 'jass-schieber',
    name: 'Jass Schieber',
    category: 'Kartenspiel',
    description: '2 Teams · Zielpunkte erreichen',
    keywords: ['jass', 'schieber', 'jassen', 'karten', 'schweiz'],
    kind: 'jassSchieber',
    minPlayers: 2,
    maxPlayers: 2,
    teams: 2,
    teamSize: 2,
    winner: 'highest',
    end: { type: 'target', target: 2500 },
    modes: SCHIEBER_MODES,
  },
  {
    id: 'jass-differenzler',
    name: 'Jass Differenzler',
    category: 'Kartenspiel',
    description: '4 Spieler · Ansage treffen, tiefste Differenz gewinnt',
    keywords: ['jass', 'differenzler', 'jassen', 'karten', 'schweiz'],
    kind: 'differenzler',
    minPlayers: 4,
    maxPlayers: 4,
    winner: 'lowest',
    end: { type: 'rounds', rounds: 12 },
  },
  {
    id: 'jass-coiffeur',
    name: 'Jass Coiffeur',
    category: 'Kartenspiel',
    description: '2 Teams · jede Spielart einmal',
    keywords: ['jass', 'coiffeur', 'jassen', 'karten', 'schweiz'],
    kind: 'coiffeur',
    minPlayers: 2,
    maxPlayers: 2,
    teams: 2,
    teamSize: 2,
    winner: 'highest',
    end: { type: 'allModes' },
    modes: COIFFEUR_MODES,
  },
  {
    id: 'qwirkle',
    name: 'Qwirkle',
    category: 'Legespiel',
    description: '2 bis 8 Spieler · höchste Summe gewinnt',
    keywords: ['qwirkle', 'lege', 'steine'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 8,
    winner: 'highest',
    end: { type: 'manual' },
  },
  {
    id: 'frei',
    name: 'Freie Zählung',
    category: 'Frei',
    description: 'Ohne Spielregeln · beliebig viele Spieler',
    keywords: ['frei', 'punkte', 'zählen', 'block', 'beliebig'],
    kind: 'rounds',
    minPlayers: 1,
    maxPlayers: null,
    winner: 'highest',
    winnerAdjustable: true,
    end: { type: 'manual' },
  },
];

export function getTemplate(id: string): GameTemplate | undefined {
  return TEMPLATES.find((t) => t.id === id);
}

function normalize(s: string): string {
  return s
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .trim();
}

/** Sucht in Name, Kategorie und Suchbegriffen. Leere Suche liefert alle Vorlagen. */
export function searchTemplates(query: string, category?: GameTemplate['category']): GameTemplate[] {
  const q = normalize(query);
  return TEMPLATES.filter((t) => {
    if (category && t.category !== category) return false;
    if (!q) return true;
    const hay = [t.name, t.category, ...t.keywords].map(normalize);
    return q.split(/\s+/).every((word) => hay.some((h) => h.includes(word)));
  });
}
