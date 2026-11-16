// Expo-Config-Plugin: signiert den Release-Build mit dem Upload-Schlüssel,
// wenn die Gradle-Eigenschaften SPIELTAFEL_UPLOAD_* gesetzt sind
// (z.B. in ~/.gradle/gradle.properties oder per -P auf der Kommandozeile).
// Ohne diese Eigenschaften bleibt die Debug-Signatur, wie im Expo-Standard.
const { withAppBuildGradle } = require('expo/config-plugins');

const MARKER = 'SPIELTAFEL_UPLOAD_STORE_FILE';

const RELEASE_SIGNING = `
        release {
            if (project.hasProperty('SPIELTAFEL_UPLOAD_STORE_FILE')) {
                storeFile file(project.property('SPIELTAFEL_UPLOAD_STORE_FILE'))
                storePassword project.property('SPIELTAFEL_UPLOAD_STORE_PASSWORD')
                keyAlias project.property('SPIELTAFEL_UPLOAD_KEY_ALIAS')
                keyPassword project.property('SPIELTAFEL_UPLOAD_KEY_PASSWORD')
            }
        }`;

function addReleaseSigning(gradle) {
  if (gradle.includes(MARKER)) return gradle;

  const signingStart = gradle.indexOf('signingConfigs {');
  if (signingStart < 0) throw new Error('withReleaseSigning: signingConfigs nicht gefunden');
  const insertAt = signingStart + 'signingConfigs {'.length;
  gradle = gradle.slice(0, insertAt) + RELEASE_SIGNING + gradle.slice(insertAt);

  const buildTypes = gradle.indexOf('buildTypes {');
  const release = gradle.indexOf('release {', buildTypes);
  const debugRef = 'signingConfig signingConfigs.debug';
  const ref = gradle.indexOf(debugRef, release);
  if (buildTypes < 0 || release < 0 || ref < 0) throw new Error('withReleaseSigning: Release-Build nicht gefunden');
  const replacement = `signingConfig project.hasProperty('${MARKER}') ? signingConfigs.release : signingConfigs.debug`;
  return gradle.slice(0, ref) + replacement + gradle.slice(ref + debugRef.length);
}

module.exports = function withReleaseSigning(config) {
  return withAppBuildGradle(config, (cfg) => {
    cfg.modResults.contents = addReleaseSigning(cfg.modResults.contents);
    return cfg;
  });
};
module.exports.addReleaseSigning = addReleaseSigning;
