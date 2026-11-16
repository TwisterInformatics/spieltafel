import { useFocusEffect } from 'expo-router';
import { useSQLiteContext } from 'expo-sqlite';
import { useCallback, useState } from 'react';

import type { Game } from '@/domain/types';

import { getGame, listGames } from './db';

/** Lädt alle Partien neu, sobald der Screen sichtbar wird. */
export function useGames(): Game[] | null {
  const db = useSQLiteContext();
  const [games, setGames] = useState<Game[] | null>(null);
  useFocusEffect(
    useCallback(() => {
      let active = true;
      listGames(db).then((g) => active && setGames(g));
      return () => {
        active = false;
      };
    }, [db]),
  );
  return games;
}

export function useGame(id: string): [Game | null | undefined, () => Promise<void>] {
  const db = useSQLiteContext();
  const [game, setGame] = useState<Game | null | undefined>(undefined);
  const reload = useCallback(async () => {
    setGame(await getGame(db, id));
  }, [db, id]);
  useFocusEffect(
    useCallback(() => {
      reload();
    }, [reload]),
  );
  return [game, reload];
}
