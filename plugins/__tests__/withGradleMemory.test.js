const { describe, expect, it } = require('@jest/globals');

const { setJvmArgs } = require('../withGradleMemory');

describe('withGradleMemory', () => {
  it('ersetzt die bestehenden JVM-Argumente und lässt andere Einträge stehen', () => {
    const props = [
      { type: 'comment', value: 'Kommentar' },
      { type: 'property', key: 'org.gradle.jvmargs', value: '-Xmx2048m -XX:MaxMetaspaceSize=512m' },
      { type: 'property', key: 'android.useAndroidX', value: 'true' },
    ];
    const result = setJvmArgs(props);
    const jvm = result.filter((p) => p.key === 'org.gradle.jvmargs');
    expect(jvm).toEqual([{ type: 'property', key: 'org.gradle.jvmargs', value: '-Xmx4096m -XX:MaxMetaspaceSize=1024m' }]);
    expect(result).toContainEqual({ type: 'property', key: 'android.useAndroidX', value: 'true' });
    expect(result).toContainEqual({ type: 'comment', value: 'Kommentar' });
  });

  it('fügt die JVM-Argumente hinzu, wenn sie fehlen', () => {
    expect(setJvmArgs([])).toEqual([{ type: 'property', key: 'org.gradle.jvmargs', value: '-Xmx4096m -XX:MaxMetaspaceSize=1024m' }]);
  });
});
