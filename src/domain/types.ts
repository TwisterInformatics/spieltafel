/** Art der Wertung. Bestimmt, welche Eingabe eine Runde hat und wie gerechnet wird. */
export type ScoringKind = 'rounds' | 'skyjo' | 'jassSchieber' | 'differenzler' | 'coiffeur' | 'wizard' | 'phase10';

export type WinnerRule = 'highest' | 'lowest';

export type EndRule =
  | { type: 'target'; target: number }
  | { type: 'rounds'; rounds: number }
  | { type: 'allModes' }
  /** Phase 10: Schluss nach der Runde, in der jemand die letzte Phase schafft. */
  | { type: 'phases'; phases: number }
  | { type: 'manual' };

export interface JassMode {
  name: string;
  factor: number;
}

export interface GameTemplate {
  id: string;
  name: string;
  category: 'Kartenspiel' | 'Würfelspiel' | 'Legespiel' | 'Frei';
  description: string;
  keywords: string[];
  kind: ScoringKind;
  minPlayers: number;
  maxPlayers: number | null;
  /** Spielt man in Teams (z.B. Jass Schieber), ist das die Anzahl Teams. */
  teams?: number;
  teamSize?: number;
  winner: WinnerRule;
  /** Darf die Siegerregel pro Partie geändert werden (freie Zählung)? */
  winnerAdjustable?: boolean;
  end: EndRule;
  /** Anzahl Runden = Karten geteilt durch Spieler (Wizard: 60 Karten). */
  roundsFromDeck?: number;
  modes?: JassMode[];
  /** Kurzanleitung in wenigen Sätzen, je ein Punkt pro Eintrag. */
  guide: string[];
  /** Ausführliche Anleitung im Netz. */
  rules?: { url: string; source: string };
}

export interface Participant {
  id: string;
  name: string;
  color: string;
  members?: string[];
}

export interface GameSettings {
  winner: WinnerRule;
  end: EndRule;
  modes?: JassMode[];
}

/** Rohdaten einer Runde. Gerechnet wird immer erst in scoring.ts. */
export type RoundData =
  | { kind: 'rounds'; scores: Record<string, number> }
  | { kind: 'skyjo'; scores: Record<string, number>; finisherId: string | null }
  | {
      kind: 'jassSchieber';
      factor: number;
      modeName: string;
      stich: Record<string, number>;
      weis: Record<string, number>;
      stoeck: Record<string, number>;
    }
  | { kind: 'differenzler'; ansage: Record<string, number>; erzielt: Record<string, number> }
  | { kind: 'coiffeur'; teamId: string; modeName: string; points: number }
  | { kind: 'wizard'; ansage: Record<string, number>; stiche: Record<string, number> }
  /** Phase 10: Restpunkte und wer in dieser Runde seine Phase geschafft hat. */
  | { kind: 'phase10'; scores: Record<string, number>; done: string[] };

export interface Game {
  id: string;
  templateId: string;
  createdAt: number;
  finishedAt: number | null;
  participants: Participant[];
  settings: GameSettings;
  rounds: RoundData[];
}
