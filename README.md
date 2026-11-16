# Spieltafel

Spieltafel ist eine App für iPhone und Android, mit der eine Spielrunde Punkte festhält und den Sieger automatisch ermittelt.

Die erste Version kennt diese Spiele:

| Spiel | Wertung |
| --- | --- |
| Skyjo | Tiefste Summe gewinnt, Ende bei 100 Punkten, Beender-Strafe wird verdoppelt |
| Jass Schieber | 2 Teams, Gegenpunkte (157 minus eigene) automatisch, Match 257, Weis, Stöck, Trumpf-Faktor |
| Jass Differenzler | Strafpunkte = Abstand zwischen Ansage und erzielten Punkten, feste Anzahl Runden |
| Jass Coiffeur | 2 Teams, jede Spielart einmal, Punkte mal Faktor der Spielart |
| Qwirkle | 2 bis 8 Spieler, höchste Summe gewinnt |
| Freie Zählung | Beliebig viele Spieler, keine Regeln, Sieger höchste oder tiefste Summe |

Alle Partien werden lokal auf dem Handy in SQLite gespeichert. Es gibt kein Konto und keinen Server.

## Loslegen

Voraussetzung ist Node.js 22 oder neuer.

```bash
npm install
npx expo start
```

Danach den QR-Code mit der App **Expo Go** auf dem Handy scannen. Mit `w` im Terminal öffnet sich die App im Browser.

## Prüfen

```bash
npm test            # Wertungslogik
npm run typecheck   # TypeScript
```

## Aufbau

| Ordner | Inhalt |
| --- | --- |
| `src/app` | Screens (Expo Router): Spielsuche, Neue Partie, Spielstand, Runde erfassen, Verlauf |
| `src/domain` | Spielvorlagen, Suche und Wertungslogik, ohne Abhängigkeit zu React Native |
| `src/data` | SQLite-Schema und Zugriff auf Partien und Runden |
| `src/components` | Gemeinsame UI-Bausteine |
| `src/constants/theme.ts` | Farben aus dem Figma-Design |

Ein neues Spiel braucht meist nur einen Eintrag in `src/domain/templates.ts`. Nur wenn die Wertung ganz neu ist, kommt eine neue Art in `src/domain/scoring.ts` und ein Eingabeformular in `src/app/partie/[id]/runde.tsx` dazu.

Runden werden als Rohdaten gespeichert (zum Beispiel Ansage und erzielte Punkte). Die Punkte rechnet die App bei jeder Anzeige neu aus. So bleiben alte Partien richtig, wenn eine Regel korrigiert wird.
