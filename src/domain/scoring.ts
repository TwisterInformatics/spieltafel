import type { Game, Participant, RoundData } from './types';

export const JASS_TOTAL = 157;
export const JASS_MATCH = 257;

/** Gegenpunkte beim Jass: 157 minus eigene Punkte, bei Match 0. */
export function jassCounterPoints(points: number): number {
  if (points >= JASS_MATCH) return 0;
  return Math.max(0, JASS_TOTAL - points);
}

/**
 * Skyjo: Wer die Runde beendet und nicht allein die tiefste Punktzahl hat,
 * bekommt seine Rundenpunkte verdoppelt (nur wenn sie positiv sind).
 */
export function skyjoRoundScores(scores: Record<string, number>, finisherId: string | null): Record<string, number> {
  const result = { ...scores };
  if (!finisherId || !(finisherId in scores)) return result;
  const own = scores[finisherId];
  const othersLowerOrEqual = Object.entries(scores).some(([id, v]) => id !== finisherId && v <= own);
  if (othersLowerOrEqual && own > 0) result[finisherId] = own * 2;
  return result;
}

/** Platzierung (Arschlöchle): Der Erste bekommt so viele Punkte, wie er Mitspieler hat, der Letzte 0. */
export function placementPoints(order: string[], participantIds: string[]): Record<string, number> {
  const n = participantIds.length;
  const full = [...order, ...participantIds.filter((id) => !order.includes(id))];
  const out: Record<string, number> = {};
  for (const id of participantIds) out[id] = n - 1 - full.indexOf(id);
  return out;
}

/** Wizard: Ansage getroffen gibt 20 plus 10 pro Stich, sonst 10 Abzug pro Stich Unterschied. */
export function wizardScore(ansage: number, stiche: number): number {
  return ansage === stiche ? 20 + 10 * stiche : -10 * Math.abs(ansage - stiche);
}

/** Phase 10: wie viele Phasen jeder schon geschafft hat. */
export function phasesDone(game: Game): Record<string, number> {
  const out: Record<string, number> = {};
  for (const p of game.participants) out[p.id] = 0;
  for (const r of game.rounds) {
    if (r.kind !== 'phase10') continue;
    for (const id of r.done) if (id in out) out[id] += 1;
  }
  return out;
}

/** Punkte, die eine einzelne Runde jedem Teilnehmer gibt. */
export function roundScores(round: RoundData, participants: Participant[]): Record<string, number> {
  const out: Record<string, number> = {};
  for (const p of participants) out[p.id] = 0;
  switch (round.kind) {
    case 'rounds':
      for (const p of participants) out[p.id] = round.scores[p.id] ?? 0;
      break;
    case 'skyjo': {
      const s = skyjoRoundScores(round.scores, round.finisherId);
      for (const p of participants) out[p.id] = s[p.id] ?? 0;
      break;
    }
    case 'jassSchieber':
      for (const p of participants) {
        const base = (round.stich[p.id] ?? 0) + (round.weis[p.id] ?? 0) + (round.stoeck[p.id] ?? 0);
        out[p.id] = base * round.factor;
      }
      break;
    case 'differenzler':
      for (const p of participants) {
        out[p.id] = Math.abs((round.ansage[p.id] ?? 0) - (round.erzielt[p.id] ?? 0));
      }
      break;
    case 'placement': {
      const pts = placementPoints(round.order, participants.map((p) => p.id));
      for (const p of participants) out[p.id] = pts[p.id];
      break;
    }
    case 'lives':
      for (const id of round.losers) if (id in out) out[id] = -1;
      break;
    case 'wizard':
      for (const p of participants) out[p.id] = wizardScore(round.ansage[p.id] ?? 0, round.stiche[p.id] ?? 0);
      break;
    case 'phase10':
      for (const p of participants) out[p.id] = round.scores[p.id] ?? 0;
      break;
    case 'coiffeur':
      // Nur das spielende Team schreibt; der Faktor wird beim Speichern mitgegeben.
      break;
  }
  return out;
}

function coiffeurFactor(game: Game, modeName: string): number {
  return game.settings.modes?.find((m) => m.name === modeName)?.factor ?? 1;
}

/** Punkte jeder Runde für jeden Teilnehmer, in Spielreihenfolge. */
export function scoreTable(game: Game): Record<string, number>[] {
  return game.rounds.map((r) => {
    if (r.kind === 'coiffeur') {
      const out: Record<string, number> = {};
      for (const p of game.participants) out[p.id] = 0;
      out[r.teamId] = r.points * coiffeurFactor(game, r.modeName);
      return out;
    }
    return roundScores(r, game.participants);
  });
}

/** Startwert jedes Teilnehmers: die Leben bei "Letzter im Spiel", sonst 0. */
export function startValue(game: Game): number {
  return game.settings.end.type === 'lastStanding' ? game.settings.end.lives : 0;
}

/** Gesamtpunkte, bei Spielen mit Leben die verbleibenden Leben. */
export function totals(game: Game): Record<string, number> {
  const sum: Record<string, number> = {};
  const start = startValue(game);
  for (const p of game.participants) sum[p.id] = start;
  for (const row of scoreTable(game)) {
    for (const [id, v] of Object.entries(row)) sum[id] = (sum[id] ?? 0) + v;
  }
  return sum;
}

/** Welche Coiffeur-Spielarten ein Team noch spielen muss. */
export function openCoiffeurModes(game: Game, teamId: string): string[] {
  const played = new Set(
    game.rounds.filter((r) => r.kind === 'coiffeur' && r.teamId === teamId).map((r) => (r as { modeName: string }).modeName),
  );
  return (game.settings.modes ?? []).map((m) => m.name).filter((n) => !played.has(n));
}

/** Ist die Partie nach den Regeln zu Ende? Manuelle Partien enden nie von selbst. */
export function isFinishedByRules(game: Game): boolean {
  const end = game.settings.end;
  switch (end.type) {
    case 'target':
      return Object.values(totals(game)).some((v) => v >= end.target);
    case 'rounds':
      return game.rounds.length >= end.rounds;
    case 'allModes':
      return (game.settings.modes?.length ?? 0) > 0 && game.participants.every((p) => openCoiffeurModes(game, p.id).length === 0);
    case 'lastStanding':
      return game.participants.length > 1 && alive(game).length <= 1;
    case 'phases': {
      const done = phasesDone(game);
      return Object.values(done).some((n) => n >= end.phases);
    }
    case 'manual':
      return false;
  }
}

/** Lässt sich eine beendete Partie weiterspielen? Nein, wenn keine weitere Runde möglich ist. */
export function canResume(game: Game): boolean {
  const end = game.settings.end;
  if (end.type === 'allModes') return game.participants.some((p) => openCoiffeurModes(game, p.id).length > 0);
  if (end.type === 'lastStanding') return alive(game).length > 1;
  return true;
}

/** Teilnehmer, die noch Leben haben. */
export function alive(game: Game): Participant[] {
  const t = totals(game);
  return game.participants.filter((p) => t[p.id] > 0);
}

export interface RankEntry {
  participant: Participant;
  total: number;
  rank: number;
  /** Nur Phase 10: geschaffte Phasen. */
  phases?: number;
}

/**
 * Rangliste nach Siegerregel. Gleiche Punkte teilen sich den Rang.
 * Phase 10: zuerst die meisten geschafften Phasen, dann die wenigsten Punkte.
 */
export function ranking(game: Game): RankEntry[] {
  const t = totals(game);
  const dir = game.settings.winner === 'highest' ? -1 : 1;
  const phases = game.settings.end.type === 'phases' ? phasesDone(game) : null;
  const sorted = [...game.participants].sort(
    (a, b) => (phases ? phases[b.id] - phases[a.id] : 0) || dir * (t[a.id] - t[b.id]),
  );
  const out: RankEntry[] = [];
  sorted.forEach((p, i) => {
    const prev = out[i - 1];
    const same = prev && prev.total === t[p.id] && (!phases || prev.phases === phases[p.id]);
    const rank = same ? prev.rank : i + 1;
    out.push({ participant: p, total: t[p.id], rank, ...(phases ? { phases: phases[p.id] } : {}) });
  });
  return out;
}
