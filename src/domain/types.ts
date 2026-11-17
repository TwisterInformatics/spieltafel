/** Art der Wertung. Bestimmt, welche Eingabe eine Runde hat und wie gerechnet wird. */
export type ScoringKind = 'rounds' | 'skyjo' | 'jassSchieber' | 'differenzler' | 'coiffeur';

export type WinnerRule = 'highest' | 'lowest';

export type EndRule =
  | { type: 'target'; target: number }
  | { type: 'rounds'; rounds: number }
  | { type: 'allModes' }
  | { type: 'manual' };

export interface JassMode {
  name: string;
  factor: number;
}

export interface GameTemplate {
  id: string;
  name: string;
  category: 'Kartenspiel' | 'Legespiel' | 'Frei';
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
  | { kind: 'coiffeur'; teamId: string; modeName: string; points: number };

export interface Game {
  id: string;
  templateId: string;
  createdAt: number;
  finishedAt: number | null;
  participants: Participant[];
  settings: GameSettings;
  rounds: RoundData[];
}
