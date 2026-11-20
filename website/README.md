# Webseite (WordPress-Theme)

Der Ordner `spieltafel/` ist ein klassisches WordPress-Theme für die Webseite zur App. Es braucht keine Plugins.

## Was drin ist

- **Startseite**: App-Symbol, Einleitung, Knöpfe zu App Store und Google Play (ohne Link steht «bald»), Ablauf in vier Schritten, alle Funktionen der App, drei Screenshots, alle Spiele mit Suche und Kategorien wie in der App, Formular für Spielwünsche.
- **Spiele & Anleitungen** (`/spiele/`): alle Spiele mit Kurzanleitung, Spieleranzahl, Siegerregel, Spielende, beim Jass die Faktoren, und dem Link zur ausführlichen Anleitung.
- **Eigene Seite pro Spiel** (`/spiele/skyjo/`, `/spiele/jass-schieber/` …), auch in der Sitemap von WordPress.
- **Spiel wünschen** (`/spiel-wuenschen/`): Formular mit Spielname, Art, Zählweise, Link, Name und E-Mail (freiwillig). Die Wünsche landen im Backend unter **Spielwünsche** und auf Wunsch per E-Mail. Spamschutz mit verstecktem Feld, Mindestzeit und höchstens 5 Wünschen pro Stunde und Adresse.

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

Das Theme lädt keine Schriften, Skripte oder Bilder von fremden Servern. Die Emoji-Skripte von WordPress sind abgeschaltet.
