import { ranking, totals } from './scoring';
import type { Game } from './types';

/** Kurzer Stand für Listen: "742 : 615" bei zwei Parteien, sonst der Führende. */
export function shortScore(game: Game): string {
  if (game.rounds.length === 0) return 'Noch keine Runde';
  const t = totals(game);
  if (game.participants.length === 2) {
    const [a, b] = game.participants;
    return `${t[a.id]} : ${t[b.id]}`;
  }
  const leader = ranking(game)[0];
  return `${leader.participant.name} ${leader.participant.name.includes(' und ') ? 'führen' : 'führt'} mit ${leader.total}`;
}

export function winnerText(game: Game): string {
  const r = ranking(game);
  const winners = r.filter((e) => e.rank === 1).map((e) => e.participant.name);
  if (winners.length === 0) return '';
  if (winners.length > 1) return `Unentschieden: ${winners.join(', ')}`;
  return `${winners[0]} ${winners[0].includes(' und ') ? 'gewinnen' : 'gewinnt'}`;
}

/** "Heute", "Gestern" oder das Datum mit Wochentag. */
export function dayLabel(ts: number, now = new Date()): string {
  const d = new Date(ts);
  const yesterday = new Date(now);
  yesterday.setDate(now.getDate() - 1);
  if (d.toDateString() === now.toDateString()) return 'Heute';
  if (d.toDateString() === yesterday.toDateString()) return 'Gestern';
  return d.toLocaleDateString('de-CH', { weekday: 'long', day: 'numeric', month: 'long' });
}

export function participantsLine(game: Game): string {
  return game.participants.map((p) => p.name).join(game.participants.length === 2 ? ' gegen ' : ', ');
}
