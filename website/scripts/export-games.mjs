// Schreibt die Spiele der App als JSON ins WordPress-Theme.
// Aufruf: npm run website:data  (Node 22.18 oder neuer, liest die TypeScript-Dateien direkt)
import { writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { TEMPLATES } from '../../src/domain/templates.ts';
import { TemplateColors } from '../../src/constants/theme.ts';

// Spiele, die im Code (main) schon da sind, aber noch nicht in der App-Version im Store.
// Nach dem nächsten Release hier entfernen und npm run website:data ausführen.
const NOCH_NICHT_IM_STORE = ['uno', 'phase-10', 'wizard', 'maexle'];

function players(t) {
  if (t.teams) return `${t.teams} Teams zu ${t.teamSize ?? 2}`;
  if (t.maxPlayers === null) return `ab ${t.minPlayers} ${t.minPlayers === 1 ? 'Spieler' : 'Spielern'}`;
  if (t.minPlayers === t.maxPlayers) return `${t.minPlayers} Spieler`;
  return `${t.minPlayers} bis ${t.maxPlayers} Spieler`;
}

function ending(t) {
  const e = t.end;
  switch (e.type) {
    case 'target':
      return t.winner === 'lowest' ? `Ende bei ${e.target} Punkten (einstellbar)` : `Ziel: ${e.target} Punkte (einstellbar)`;
    case 'rounds':
      return t.roundsFromDeck ? `Runden nach Anzahl Karten (${t.roundsFromDeck} geteilt durch Spieler)` : `${e.rounds} Runden (einstellbar)`;
    case 'allModes':
      return 'Ende, wenn alle Spielarten gespielt sind';
    case 'lastStanding':
      return `${e.lives} Leben pro Spieler (einstellbar)`;
    default:
      return 'Ende, wann ihr wollt';
  }
}

function winner(t) {
  if (t.winnerAdjustable) return 'Höchste oder tiefste Summe gewinnt, wählbar';
  if (t.end.type === 'lastStanding') return 'Wer übrig bleibt, gewinnt';
  return t.winner === 'lowest' ? 'Tiefste Summe gewinnt' : 'Höchste Summe gewinnt';
}

const games = TEMPLATES.map((t) => ({
  id: t.id,
  name: t.name,
  category: t.category,
  description: t.description,
  keywords: t.keywords,
  color: TemplateColors[t.id] ?? '#1f6f4a',
  players: players(t),
  winner: winner(t),
  end: ending(t),
  modes: t.modes ?? [],
  guide: t.guide,
  rules: t.rules ?? null,
  upcoming: NOCH_NICHT_IM_STORE.includes(t.id),
}));

const out = fileURLToPath(new URL('../spieltafel/data/games.json', import.meta.url));
writeFileSync(out, JSON.stringify(games, null, 2) + '\n');
console.log(`${games.length} Spiele nach ${out} geschrieben.`);
