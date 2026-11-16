import { useKeepAwake } from 'expo-keep-awake';
import { useSQLiteContext } from 'expo-sqlite';
import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';

import { getAppSetting, setAppSetting } from './db';

const KEEP_AWAKE_KEY = 'keepScreenOn';
/** Standard: Bildschirm bleibt an, weil das Handy beim Spielen meist auf dem Tisch liegt. */
const KEEP_AWAKE_DEFAULT = true;

interface AppSettings {
  keepScreenOn: boolean;
  setKeepScreenOn: (on: boolean) => void;
}

const SettingsContext = createContext<AppSettings | null>(null);

/** Lädt die App-Einstellungen aus SQLite und hält bei Bedarf den Bildschirm an. */
export function SettingsProvider({ children }: { children: ReactNode }) {
  const db = useSQLiteContext();
  const [keepScreenOn, setKeepScreenOnState] = useState(KEEP_AWAKE_DEFAULT);

  useEffect(() => {
    let active = true;
    getAppSetting(db, KEEP_AWAKE_KEY).then((v) => {
      if (active && v !== null) setKeepScreenOnState(v === '1');
    });
    return () => {
      active = false;
    };
  }, [db]);

  const setKeepScreenOn = useCallback(
    (on: boolean) => {
      setKeepScreenOnState(on);
      setAppSetting(db, KEEP_AWAKE_KEY, on ? '1' : '0');
    },
    [db],
  );

  const value = useMemo(() => ({ keepScreenOn, setKeepScreenOn }), [keepScreenOn, setKeepScreenOn]);
  return (
    <SettingsContext.Provider value={value}>
      {keepScreenOn ? <ScreenAwake /> : null}
      {children}
    </SettingsContext.Provider>
  );
}

/** Solange diese Komponente gerendert ist, sperrt sich das Gerät nicht. */
function ScreenAwake() {
  useKeepAwake('spieltafel-einstellung');
  return null;
}

export function useSettings(): AppSettings {
  const ctx = useContext(SettingsContext);
  if (!ctx) throw new Error('useSettings ausserhalb von SettingsProvider');
  return ctx;
}
