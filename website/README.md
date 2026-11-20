# Webseite (WordPress-Theme)

Der Ordner `spieltafel/` ist ein klassisches WordPress-Theme für die Webseite zur App. Es braucht keine Plugins.

## Was drin ist

- **Startseite**: App-Symbol, Einleitung, Knöpfe zu App Store und Google Play (ohne Link steht «bald»), Ablauf in vier Schritten, alle Funktionen der App, drei Screenshots, alle Spiele mit Suche und Kategorien wie in der App, Formular für Spielwünsche.
- **Spiele & Anleitungen** (`/spiele/`): alle Spiele mit Kurzanleitung, Spieleranzahl, Siegerregel, Spielende, beim Jass die Faktoren, und dem Link zur ausführlichen Anleitung.
- **Eigene Seite pro Spiel** (`/spiele/skyjo/`, `/spiele/jass-schieber/` …), auch in der Sitemap von WordPress.
- **Spiel wünschen** (`/spiel-wuenschen/`): Formular mit Spielname, Art, Zählweise, Link, Name und E-Mail (freiwillig). Die Wünsche landen im Backend unter **Spielwünsche** und auf Wunsch per E-Mail. Spamschutz mit verstecktem Feld, Mindestzeit und höchstens 5 Wünschen pro Stunde und Adresse.

## Spieltreff: Mitspieler finden

Plattform nur auf der Webseite (`/spieltreff/`), Code in `spieltafel/inc/spieltreff/`.

- **Angebote** mit Datum, Zeit, Ort und Spielen (Auswahl aus der App-Liste plus Freitext), Plätzen, Anmeldeschluss und «Anfänger willkommen».
- **Gesuche** mit freiem Zeitpunkt («jeden Donnerstag»), laufen nach 90 Tagen ab und lassen sich verlängern.
- **Anmeldung**: Der Ersteller bestätigt jede Anmeldung (oder wählt sofort definitiv). Sind alle Plätze bestätigt, kommen weitere auf die **Warteliste** und rücken automatisch nach. Option «findet nur statt bei genügend Anmeldungen» mit Mindestanzahl. Absagen informiert alle per E-Mail. Nachricht an alle Angemeldeten, Kalenderdatei (.ics) für Bestätigte.
- **Datenschutz**: Öffentlich sind nur Anzeigename, PLZ/Ort/Kanton. Genaue Adresse und Kontakt sehen nur Bestätigte, die E-Mail der Teilnehmenden nur der Ersteller. Einträge sind `noindex` und nicht in der Sitemap. Vergangene Einträge werden nach 30 Tagen gelöscht, unbestätigte Konten nach 7 Tagen. Im Konto: Daten als JSON herunterladen, Konto löschen. Angebunden an die Datenschutz-Werkzeuge von WordPress, Textvorschlag für die Datenschutzerklärung.
- **Konten**: Rolle «Spieler», Registrierung mit Anzeigename, E-Mail, Passwort, Zustimmung zu Regeln und Mindestalter, Bestätigung per E-Mail-Link. Spieler sehen das Backend nicht.
- **Spamschutz ohne Drittanbieter**: Rechenaufgabe im Browser (Proof of Work nach ALTCHA-Verfahren), verstecktes Feld, Mindestzeit, Begrenzungen pro Adresse (nur als Hash), Sperre nach 8 Fehlversuchen beim Login. Gilt auch für das Spielwunsch-Formular.
- **Teilen** auf Facebook, Instagram und WhatsApp sowie Link kopieren. Ohne Skripte von Meta: Facebook und WhatsApp sind normale Links. Für Instagram erzeugt das Theme ein Story-Bild (1080 × 1920), das auf dem Handy über das Teilen-Menü direkt an Instagram geht; am Computer wird es heruntergeladen und der Link kopiert (für den Link-Sticker). Facebook und WhatsApp zeigen eine Vorschau mit eigenem Bild (Open Graph, 1200 × 630), ohne Adresse. Bilder werden mit PHP-GD gezeichnet, Schrift Inter (OFL) liegt in `assets/fonts/`.
- **Moderation**: Erster Eintrag jeder Person wird geprüft (einstellbar), «Eintrag melden», nach 3 Meldungen ausgeblendet, Konten sperren unter *Benutzer*.

- **Kampagne «Zurück an den Tisch»** (`inc/spieltreff/campaign.php`): Kernbotschaft sich wieder zu treffen und gemeinsam am Tisch zu spielen statt zu scrollen. Vorschau («Bald neu») und Live-Seite haben einen grünen Kopf mit Illustration (Spieltisch von oben, das Handy liegt umgedreht daneben), drei Gründe, die Handystapel-Regel und einen Satz mit Augenzwinkern zur Webseite. Unter *Spieltreff → Einstellungen → Bilder für Social Media* gibt es 18 fertige Bilder (3 Sätze × Facebook/WhatsApp, Instagram-Beitrag, Story, je vor und nach dem Start) mit der Adresse der Webseite. Die Übersicht `/spieltreff/` hat dazu eine eigene Vorschau für Facebook und WhatsApp.

**Freischalten**: *Spieltreff → Einstellungen → Spieltreff für alle freischalten*. Vorher sehen Besucher nur einen Hinweis («Bald neu»), angemeldete Benutzer können alles testen. Testkonten unter *Benutzer → Neu hinzufügen* mit Rolle «Spieler» anlegen, oder dort «Registrierung schon vor der Freischaltung öffnen» anhaken. Die Regeln liegen als Seite `spieltreff-regeln` vor und sollten vor dem Start geprüft werden.

## Installieren

1. Zip hochladen unter *Design → Themes → Theme hinzufügen → Theme hochladen*, dann aktivieren.
2. Beim Aktivieren legt das Theme die Seiten «Spiele & Anleitungen» und «Spiel wünschen» an und stellt die Permalinks auf `/%postname%/`, falls noch «Einfach» eingestellt war.
3. Unter *Design → Customizer → Spieltafel*: Titel und Einleitung, Store-Links, Empfänger sowie Absender-Adresse und -Name der Wunsch-E-Mails. Die Absender-Adresse sollte zur Domain der Website gehören, sonst landen die E-Mails leicht im Spam.
4. Unter *Einstellungen → Datenschutz* eine Datenschutzseite wählen. Das Theme schlägt dort einen Text für das Formular vor. Für die Schweiz zusätzlich eine Seite mit der Adresse `impressum` anlegen, sie erscheint dann automatisch in der Fusszeile.
5. Menüs sind optional. Ohne eigenes Menü zeigt die Kopfzeile Funktionen, Spiele & Anleitungen und Spiel wünschen.

Damit E-Mails zuverlässig ankommen, empfiehlt sich ein SMTP-Plugin. Die Wünsche werden auch ohne E-Mail im Backend gespeichert.

## Spiele aktualisieren

Die Spiele, Kurzanleitungen und Links kommen direkt aus der App (`src/domain/templates.ts`). Nach einer Änderung dort:

```bash
npm run website:data
```

Das schreibt `website/spieltafel/data/games.json` neu. Ein Test (`npm test`) schlägt fehl, wenn die Webseite nicht mehr zur App passt. Die Texte zu den Funktionen stehen in `spieltafel/inc/content.php`.

## Zip bauen

```bash
cd website && zip -r spieltafel-theme.zip spieltafel
```

## Datenschutz

Das Theme lädt keine Schriften, Skripte oder Bilder von fremden Servern, auch der Spamschutz läuft ohne fremde Dienste. Die Emoji-Skripte von WordPress sind abgeschaltet.
