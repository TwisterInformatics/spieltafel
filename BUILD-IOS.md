# iPhone- und iPad-App in den App Store bringen

Diese Anleitung baut die App für iPhone und iPad und lädt sie zu App Store Connect hoch. Der Code ist derselbe wie für Android.

## Was schon vorbereitet ist

| Was | Wo |
| --- | --- |
| Bundle-ID `ch.spieltafel.app`, iPad freigeschaltet, Version 1.0.0 | `app.json` |
| Angabe "keine eigene Verschlüsselung" (spart die Rückfrage bei jedem Upload) | `app.json`, `ios.config.usesNonExemptEncryption` |
| App-Symbol 1024 × 1024 ohne Transparenz | `assets/images/icon.png` |
| Store-Build mit automatisch hochgezählter Build-Nummer | `eas.json`, Profil `production` |
| Store-Texte auf Deutsch, Kategorien, Altersfreigabe | `store.config.json` |
| Screenshots für iPhone 6,9" und iPad 13" | `assets/store/ios/` |
| Entwurf der Datenschutzerklärung | `DATENSCHUTZ.md` |

## Was nur du machen kannst

1. **Apple Developer Program**: Mitgliedschaft (99 USD pro Jahr) unter <https://developer.apple.com/programs/>. Ein kostenloses Entwicklerkonto reicht für den Simulator und das eigene Gerät, nicht für den Store.
2. **Expo-Konto**: kostenlos unter <https://expo.dev/signup>. Der Build läuft dort in der Cloud.
3. **Datenschutzerklärung veröffentlichen**: Den Text aus `DATENSCHUTZ.md` ausfüllen und unter einer öffentlichen Adresse ablegen. Apple verlangt diese Adresse für jede App.
4. **Support-Adresse**: eine Webseite oder Kontaktseite, auch sie ist Pflicht.

Die beiden Adressen danach in `store.config.json` bei `de-DE` ergänzen:

```json
"privacyPolicyUrl": "https://…",
"supportUrl": "https://…"
```

## 1. Anmelden und Projekt verknüpfen

```bash
npm ci
```

```bash
npx eas-cli@latest login
```

```bash
npx eas-cli@latest init
```

`init` legt das Projekt bei Expo an und trägt die Projekt-ID in `app.json` ein. Diese Änderung committen.

## 2. Store-Build erstellen

```bash
npx eas-cli@latest build --platform ios --profile production
```

Beim ersten Mal fragt EAS nach der Apple-ID und legt Zertifikat und Provisioning-Profil selbst an. Mit "Yes" bestätigen. Der Build dauert etwa 15 bis 25 Minuten und endet mit einem Link zur `.ipa`-Datei.

Die Build-Nummer verwaltet Expo (`appVersionSource: remote`) und zählt sie bei jedem Build hoch. Für eine neue Store-Version nur `version` in `app.json` erhöhen.

## 3. Zu App Store Connect hochladen

```bash
npx eas-cli@latest submit --platform ios --latest
```

Beim ersten Mal legt EAS die App in App Store Connect an (Name "Spieltafel", Sprache Deutsch). Ist der Name im Store schon vergeben, fragt EAS nach einem anderen. Nach einigen Minuten erscheint der Build unter **TestFlight**.

## 4. Auf dem eigenen Gerät testen

In App Store Connect unter **TestFlight** sich selbst als internen Tester eintragen und die App über die TestFlight-App auf iPhone und iPad installieren. Mindestens eine Partie durchspielen, auch im Querformat auf dem iPad.

## 5. Store-Eintrag füllen

Texte, Kategorien und Altersfreigabe hochladen:

```bash
npx eas-cli@latest metadata:push
```

EAS Metadata ist noch Beta. Wenn der Befehl scheitert, die Texte aus `store.config.json` von Hand in App Store Connect einfügen.

Von Hand in App Store Connect:

1. **Screenshots** aus `assets/store/ios/` hochladen: `iphone-*.png` bei "iPhone 6,9-Zoll-Display", `ipad-*.png` bei "iPad 13-Zoll-Display".
2. **App-Datenschutz**: "Es werden keine Daten erfasst" wählen. Die App speichert alles lokal und sendet nichts.
3. **Preis**: kostenlos, Länder auswählen.
4. **Informationen für die App-Prüfung**: Name, E-Mail und Telefon einer Kontaktperson. Ein Demo-Konto braucht es nicht.
5. Build aus Schritt 3 auswählen und **Zur Prüfung einreichen**.

Die Prüfung dauert meist ein bis zwei Tage. Die Freigabe ist auf "manuell" gestellt: Nach der Genehmigung veröffentlichst du die App selbst per Knopf.

## Gut zu wissen

- **Markennamen**: Die Store-Texte nennen bewusst nur Spiele ohne geschützte Marke. Skyjo, Uno, Phase 10, Wizard und Qwirkle stehen in der App, aber nicht in Beschreibung und Suchbegriffen. Apple lehnt fremde Marken in den Store-Texten oft ab.
- **Altersfreigabe**: Alle Fragen sind mit "keine" beantwortet. Wer den Spielnamen "Arschlöchle" als derbe Sprache einstuft, setzt in `store.config.json` `profanityOrCrudeHumor` auf `INFREQUENT_OR_MILD`.
- **Ordner `ios/`**: wird erzeugt und ist nicht im Repository. Nie von Hand ändern, Einstellungen gehören in `app.json`.

## Lokal im Simulator starten

Voraussetzung ist Xcode.

```bash
npx expo run:ios
```

## Häufige Fehler

| Meldung | Lösung |
| --- | --- |
| `You have no team associated with your Apple account` | Die Mitgliedschaft im Apple Developer Program fehlt oder ist noch nicht aktiv |
| `The app name you entered is already being used` | Anderen Namen für den Store wählen, zum Beispiel "Spieltafel – Punkte zählen". Der Name unter dem App-Symbol bleibt "Spieltafel" |
| `Bundle identifier is not available` | Die Bundle-ID gehört schon einem anderen Konto. In `app.json` unter `ios.bundleIdentifier` eine eigene eintragen |
| App Store Connect: "Missing Compliance" | Sollte nicht erscheinen. Prüfen, ob `usesNonExemptEncryption` in `app.json` noch steht |
