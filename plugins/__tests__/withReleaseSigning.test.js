const { describe, expect, it } = require('@jest/globals');

const { addReleaseSigning } = require('../withReleaseSigning');

const SAMPLE = `android {
    signingConfigs {
        debug {
            storeFile file('debug.keystore')
        }
    }
    buildTypes {
        debug {
            signingConfig signingConfigs.debug
        }
        release {
            signingConfig signingConfigs.debug
            minifyEnabled false
        }
    }
}`;

describe('withReleaseSigning', () => {
  it('nutzt den Upload-Schlüssel nur im Release-Build', () => {
    const out = addReleaseSigning(SAMPLE);
    expect(out).toContain("storeFile file(project.property('SPIELTAFEL_UPLOAD_STORE_FILE'))");
    const debugBlock = out.slice(out.indexOf('buildTypes {'), out.indexOf('release {', out.indexOf('buildTypes {')));
    expect(debugBlock).toContain('signingConfig signingConfigs.debug');
    expect(out).toContain("signingConfig project.hasProperty('SPIELTAFEL_UPLOAD_STORE_FILE') ? signingConfigs.release : signingConfigs.debug");
  });
  it('ändert nichts beim zweiten Durchlauf', () => {
    const once = addReleaseSigning(SAMPLE);
    expect(addReleaseSigning(once)).toBe(once);
  });
});
