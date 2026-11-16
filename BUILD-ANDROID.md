# Android App Bundle (AAB) auf dem eigenen PC bauen

Diese Anleitung baut die Play-Store-Datei `app-release.aab` auf einem Windows-, macOS- oder Linux-PC, auf dem Android Studio schon eingerichtet ist. Dauer beim ersten Mal etwa 15 bis 30 Minuten, danach wenige Minuten.

## 1. Voraussetzungen prüfen

| Was | Version | Prüfen mit |
| --- | --- | --- |
| Node.js | 22 oder neuer | `node -v` |
| Java (JDK) | 17 | `java -version` |
| Android SDK | über Android Studio, mit "Android SDK Platform" und "Build-Tools" | `ANDROID_HOME` ist gesetzt |
| Git | beliebig | `git --version` |

Android Studio bringt ein passendes JDK mit. Falls `java -version` etwas anderes als 17 zeigt, `JAVA_HOME` auf das JDK von Android Studio setzen:

- Windows: `C:\Program Files\Android\Android Studio\jbr`
- macOS: `/Applications/Android Studio.app/Contents/jbr/Contents/Home`

`ANDROID_HOME` zeigt auf das SDK, meistens:

- Windows: `C:\Users\<Name>\AppData\Local\Android\Sdk`
- macOS: `~/Library/Android/sdk`
- Linux: `~/Android/Sdk`

## 2. Upload-Schlüssel ablegen

Die Datei `spieltafel-upload.jks` und das Passwort stehen in `ANLEITUNG.md` im Projektordner `spieltafel-signing`.

1. `spieltafel-upload.jks` an einen sicheren Ort kopieren, **nicht** in den Projektordner. Beispiel: `C:/Users/<Name>/keys/spieltafel-upload.jks`.
2. Die Datei `gradle.properties` im Gradle-Benutzerordner öffnen oder neu anlegen:
   - Windows: `C:\Users\<Name>\.gradle\gradle.properties`
   - macOS und Linux: `~/.gradle/gradle.properties`
3. Diese vier Zeilen eintragen. Pfad mit normalen Schrägstrichen `/`, auch unter Windows:

```properties
SPIELTAFEL_UPLOAD_STORE_FILE=C:/Users/<Name>/keys/spieltafel-upload.jks
SPIELTAFEL_UPLOAD_STORE_PASSWORD=<Passwort aus ANLEITUNG.md>
SPIELTAFEL_UPLOAD_KEY_ALIAS=spieltafel-upload
SPIELTAFEL_UPLOAD_KEY_PASSWORD=<Passwort aus ANLEITUNG.md>
```

So bleibt das Passwort ausserhalb des Repositorys. Das Plugin `plugins/withReleaseSigning.js` liest diese Werte beim Release-Build.

## 3. Code holen

```bash
git clone https://github.com/TwisterInformatics/spieltafel.git
cd spieltafel
npm ci
```

Solange der Pull Request noch nicht gemergt ist, liegt der Code auf dem Branch `erste-version`. Dann vor `npm ci` noch `git checkout erste-version` ausführen.

## 4. Versionsnummer erhöhen

Google Play verlangt bei jedem Upload eine höhere `versionCode`. In `app.json` anpassen:

```json
"version": "1.0.0",
"android": {
  "versionCode": 1
}
```

Für den ersten Upload passt `1`. Bei jedem weiteren Upload `versionCode` um 1 erhöhen, und `version` für die Anzeige im Store bei Bedarf ebenfalls.

## 5. Android-Projekt erzeugen und bauen

```bash
npx expo prebuild --platform android --clean
cd android
```

Dann bauen:

- Windows (PowerShell oder cmd): `.\gradlew.bat bundleRelease`
- macOS und Linux: `./gradlew bundleRelease`

Der erste Build lädt Gradle und Bibliotheken und dauert länger. Am Ende steht `BUILD SUCCESSFUL`.

Die fertige Datei liegt hier:

```
android/app/build/outputs/bundle/release/app-release.aab
```

## 6. Signatur prüfen

Im Ordner `spieltafel` (eine Ebene über `android`):

```bash
keytool -printcert -jarfile android/app/build/outputs/bundle/release/app-release.aab
```

Bei **Eigentümer** muss `CN=Spieltafel, O=Spieltafel, C=CH` stehen. Steht dort `CN=Android Debug`, wurden die Einträge aus Schritt 2 nicht gefunden: Pfad und Dateiname von `gradle.properties` prüfen und Schritt 5 wiederholen.

## 7. In Google Play hochladen

1. In der [Google Play Console](https://play.google.com/console) die App anlegen (Name "Spieltafel", kostenlos).
2. Unter **Testen → Interner Test** einen Release erstellen und `app-release.aab` hochladen.
3. Google übernimmt beim ersten Upload das endgültige Signieren ("Play App Signing"). Unser Schlüssel ist nur der Upload-Schlüssel und kann bei Verlust über den Play-Support ersetzt werden.

## Häufige Fehler

| Meldung | Lösung |
| --- | --- |
| `SDK location not found` | `ANDROID_HOME` setzen oder in `android/local.properties` die Zeile `sdk.dir=C:/Users/<Name>/AppData/Local/Android/Sdk` eintragen |
| `Unsupported class file major version` | Falsches Java. `JAVA_HOME` auf JDK 17 setzen (siehe Schritt 1) |
| `Keystore was tampered with, or password was incorrect` | Passwort in `gradle.properties` prüfen |
| Google Play: "Version code 1 has already been used" | `versionCode` in `app.json` erhöhen, Schritt 5 wiederholen |
| Build hängt bei "Downloading" | Firmen-Proxy oder Firewall; einmal mit Internetzugang ohne Proxy bauen |
