# Spieltafel

Spieltafel ist eine App für iPhone und Android, mit der eine Spielrunde Punkte festhält und den Sieger automatisch ermittelt.

Die App kennt diese Spiele. Zu jedem gibt es eine Kurzanleitung und, wo vorhanden, einen Link zu einer ausführlichen Anleitung:

| Spiel | Wertung |
| --- | --- |
| Skyjo | Tiefste Summe gewinnt, Ende bei 100 Punkten, Beender-Strafe wird verdoppelt |
| Jass Schieber | 2 Teams, Gegenpunkte (157 minus eigene) automatisch, Match 257, Weis, Stöck, Trumpf-Faktor |
| Jass Differenzler | Strafpunkte = Abstand zwischen Ansage und erzielten Punkten, feste Anzahl Runden |
| Jass Coiffeur | 2 Teams, jede Spielart einmal, Punkte mal Faktor der Spielart |
| Tschau Sepp | Restkarten als Strafpunkte, wenig gewinnt, Ende von Hand |
| Arschlöchle | Punkte nach Platz (König = Spieler minus 1, Arschloch 0) |
| Gämsch | 2 bis 4 Teams, Punkte pro Gämsch |
| Schwimmen (31) | Leben (Vorgabe 4 = 3 plus Schwimmen), der Letzte im Spiel gewinnt |
| Uno | Strafpunkte, wer 500 erreicht verliert |
| Phase 10 | Strafpunkte, Ende von Hand |
| Rommé | Strafpunkte, Ende von Hand |
| Stiche ansagen | Punkte von Hand, Ende von Hand |
| Wizard | Ansage und Stiche, 20 plus 10 je Stich oder 10 Abzug je Stich daneben, Runden = 60 Karten geteilt durch Spieler |
| Qwirkle | 2 bis 8 Spieler, höchste Summe gewinnt |
| Mäxle | Würfelspiel mit Leben (Vorgabe 6) |
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

Ein neues Spiel braucht meist nur einen Eintrag in `src/domain/templates.ts`, inklusive Kurzanleitung (`guide`) und Link (`rules`). Nur wenn die Wertung ganz neu ist, kommt eine neue Art in `src/domain/scoring.ts` und ein Eingabeformular in `src/app/partie/[id]/runde.tsx` dazu.

Runden werden als Rohdaten gespeichert (zum Beispiel Ansage und erzielte Punkte). Die Punkte rechnet die App bei jeder Anzeige neu aus. So bleiben alte Partien richtig, wenn eine Regel korrigiert wird.

## Tablets

Ab einer kürzeren Bildschirmseite von 600 dp gilt ein Gerät als Tablet. Dann werden Schrift, Abstände und Bedienelemente um 30 % grösser und der Inhalt wird auf höchstens 860 dp Breite zentriert (`src/constants/layout.ts`). Auf dem iPad ist die App freigeschaltet (`supportsTablet`), und sie dreht sich mit dem Gerät.

## iPhone und iPad für den App Store

Schritt für Schritt in [BUILD-IOS.md](BUILD-IOS.md). Store-Texte stehen in `store.config.json`, Screenshots in `assets/store/ios/`, der Entwurf der Datenschutzerklärung in [DATENSCHUTZ.md](DATENSCHUTZ.md).

## Android App Bundle (AAB) für Google Play

Es gibt drei Wege.

**Eigener PC mit Android Studio**: Schritt für Schritt in [BUILD-ANDROID.md](BUILD-ANDROID.md).

**GitHub Actions** (`.github/workflows/android-aab.yml`): Bei jedem Push auf `main`, bei Pull Requests und per Knopf unter *Actions → Android AAB → Run workflow* entsteht ein AAB. Es liegt danach im Lauf unter *Artifacts* zum Download. Damit Google Play es annimmt, braucht das Repository vier Secrets mit dem Upload-Schlüssel (Namen stehen oben im Workflow). Ohne Secrets entsteht nur ein Test-AAB. Die `versionCode` ist die Laufnummer des Workflows.

**EAS Build** (Expo-Cloud): Auf einem Computer mit Node.js `npx eas-cli@latest build --platform android --profile production` ausführen. Expo legt beim ersten Mal den Upload-Schlüssel an und verwaltet ihn. Dafür braucht es ein kostenloses Expo-Konto.
