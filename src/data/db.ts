import type { SQLiteDatabase } from 'expo-sqlite';

import type { Game, GameSettings, Participant, RoundData } from '@/domain/types';

export const DATABASE_NAME = 'spieltafel.db';
const SCHEMA_VERSION = 2;

/** Legt die Tabellen an. Neue Schema-Versionen hier als weitere Schritte ergänzen. */
export async function migrate(db: SQLiteDatabase): Promise<void> {
  const row = await db.getFirstAsync<{ user_version: number }>('PRAGMA user_version');
  const current = row?.user_version ?? 0;
  if (current >= SCHEMA_VERSION) return;
  if (current < 1) {
    await db.execAsync(`
      PRAGMA journal_mode = WAL;
      CREATE TABLE IF NOT EXISTS games (
        id TEXT PRIMARY KEY NOT NULL,
        template_id TEXT NOT NULL,
        created_at INTEGER NOT NULL,
        finished_at INTEGER,
        participants TEXT NOT NULL,
        settings TEXT NOT NULL
      );
      CREATE TABLE IF NOT EXISTS rounds (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id TEXT NOT NULL REFERENCES games(id) ON DELETE CASCADE,
        idx INTEGER NOT NULL,
        data TEXT NOT NULL
      );
      CREATE INDEX IF NOT EXISTS rounds_game ON rounds(game_id, idx);
      CREATE TABLE IF NOT EXISTS players (
        name TEXT PRIMARY KEY NOT NULL,
        last_used INTEGER NOT NULL
      );
    `);
  }
  if (current < 2) {
    await db.execAsync(`
      CREATE TABLE IF NOT EXISTS app_settings (
        key TEXT PRIMARY KEY NOT NULL,
        value TEXT NOT NULL
      );
    `);
  }
  await db.execAsync(`PRAGMA user_version = ${SCHEMA_VERSION}`);
}

/** Gespeicherter Wert einer App-Einstellung oder null, wenn noch nie gesetzt. */
export async function getAppSetting(db: SQLiteDatabase, key: string): Promise<string | null> {
  const row = await db.getFirstAsync<{ value: string }>('SELECT value FROM app_settings WHERE key = ?', key);
  return row?.value ?? null;
}

export async function setAppSetting(db: SQLiteDatabase, key: string, value: string): Promise<void> {
  await db.runAsync(
    'INSERT INTO app_settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value',
    key,
    value,
  );
}

interface GameRow {
  id: string;
  template_id: string;
  created_at: number;
  finished_at: number | null;
  participants: string;
  settings: string;
}

function newId(): string {
  return `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}

export async function createGame(
  db: SQLiteDatabase,
  templateId: string,
  participants: Participant[],
  settings: GameSettings,
): Promise<string> {
  const id = newId();
  const now = Date.now();
  await db.withTransactionAsync(async () => {
    await db.runAsync(
      'INSERT INTO games (id, template_id, created_at, finished_at, participants, settings) VALUES (?, ?, ?, NULL, ?, ?)',
      id,
      templateId,
      now,
      JSON.stringify(participants),
      JSON.stringify(settings),
    );
    const names = participants.flatMap((p) => p.members ?? [p.name]);
    for (const name of names) {
      await db.runAsync(
        'INSERT INTO players (name, last_used) VALUES (?, ?) ON CONFLICT(name) DO UPDATE SET last_used = excluded.last_used',
        name,
        now,
      );
    }
  });
  return id;
}

export async function getGame(db: SQLiteDatabase, id: string): Promise<Game | null> {
  const row = await db.getFirstAsync<GameRow>('SELECT * FROM games WHERE id = ?', id);
  if (!row) return null;
  const rounds = await db.getAllAsync<{ data: string }>('SELECT data FROM rounds WHERE game_id = ? ORDER BY idx', id);
  return toGame(row, rounds.map((r) => JSON.parse(r.data) as RoundData));
}

function toGame(row: GameRow, rounds: RoundData[]): Game {
  return {
    id: row.id,
    templateId: row.template_id,
    createdAt: row.created_at,
    finishedAt: row.finished_at,
    participants: JSON.parse(row.participants) as Participant[],
    settings: JSON.parse(row.settings) as GameSettings,
    rounds,
  };
}

/** Alle Partien mit ihren Runden, neueste zuerst. */
export async function listGames(db: SQLiteDatabase): Promise<Game[]> {
  const rows = await db.getAllAsync<GameRow>('SELECT * FROM games ORDER BY created_at DESC');
  const rounds = await db.getAllAsync<{ game_id: string; data: string }>('SELECT game_id, data FROM rounds ORDER BY idx');
  const byGame = new Map<string, RoundData[]>();
  for (const r of rounds) {
    const list = byGame.get(r.game_id) ?? [];
    list.push(JSON.parse(r.data) as RoundData);
    byGame.set(r.game_id, list);
  }
  return rows.map((row) => toGame(row, byGame.get(row.id) ?? []));
}

export async function addRound(db: SQLiteDatabase, gameId: string, data: RoundData): Promise<void> {
  const row = await db.getFirstAsync<{ n: number }>('SELECT COUNT(*) AS n FROM rounds WHERE game_id = ?', gameId);
  await db.runAsync('INSERT INTO rounds (game_id, idx, data) VALUES (?, ?, ?)', gameId, row?.n ?? 0, JSON.stringify(data));
}

/** Korrektur: letzte Runde löschen und Partie wieder öffnen. */
export async function deleteLastRound(db: SQLiteDatabase, gameId: string): Promise<void> {
  await db.withTransactionAsync(async () => {
    await db.runAsync(
      'DELETE FROM rounds WHERE id = (SELECT id FROM rounds WHERE game_id = ? ORDER BY idx DESC LIMIT 1)',
      gameId,
    );
    await db.runAsync('UPDATE games SET finished_at = NULL WHERE id = ?', gameId);
  });
}

export async function finishGame(db: SQLiteDatabase, gameId: string): Promise<void> {
  await db.runAsync('UPDATE games SET finished_at = ? WHERE id = ? AND finished_at IS NULL', Date.now(), gameId);
}

/** Abgeschlossene Partie wieder aufnehmen, optional mit neuer Endregel (z.B. ohne Ziel weiter). */
export async function resumeGame(db: SQLiteDatabase, gameId: string, settings?: GameSettings): Promise<void> {
  if (settings) {
    await db.runAsync('UPDATE games SET finished_at = NULL, settings = ? WHERE id = ?', JSON.stringify(settings), gameId);
  } else {
    await db.runAsync('UPDATE games SET finished_at = NULL WHERE id = ?', gameId);
  }
}

export async function deleteGame(db: SQLiteDatabase, gameId: string): Promise<void> {
  await db.withTransactionAsync(async () => {
    await db.runAsync('DELETE FROM rounds WHERE game_id = ?', gameId);
    await db.runAsync('DELETE FROM games WHERE id = ?', gameId);
  });
}

/** Zuletzt verwendete Spielernamen für Vorschläge. */
export async function recentPlayers(db: SQLiteDatabase, limit = 12): Promise<string[]> {
  const rows = await db.getAllAsync<{ name: string }>('SELECT name FROM players ORDER BY last_used DESC LIMIT ?', limit);
  return rows.map((r) => r.name);
}
