// Expo-Config-Plugin: gibt Gradle mehr Speicher. Mit dem Expo-Standard
// (-Xmx2048m, Metaspace 512m) bricht der Release-Build mit
// "OutOfMemoryError: Metaspace" ab.
const { withGradleProperties } = require('expo/config-plugins');

const KEY = 'org.gradle.jvmargs';
const JVM_ARGS = '-Xmx4096m -XX:MaxMetaspaceSize=1024m';

function setJvmArgs(props) {
  const rest = props.filter((p) => !(p.type === 'property' && p.key === KEY));
  return [...rest, { type: 'property', key: KEY, value: JVM_ARGS }];
}

module.exports = function withGradleMemory(config) {
  return withGradleProperties(config, (cfg) => {
    cfg.modResults = setJvmArgs(cfg.modResults);
    return cfg;
  });
};
module.exports.setJvmArgs = setJvmArgs;
