import { StyleSheet, Switch, Text, View } from 'react-native';

import { Card, Label, Screen, Title, useUi } from '@/components/ui';
import { useScaledStyles } from '@/constants/layout';
import { Colors } from '@/constants/theme';
import { useSettings } from '@/data/settings';

export default function SettingsScreen() {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const { keepScreenOn, setKeepScreenOn } = useSettings();

  return (
    <Screen>
      <Title>Einstellungen</Title>
      <Label>Anzeige</Label>
      <Card>
        <View style={s.text}>
          <Text style={ui.cardTitle}>Bildschirm anlassen</Text>
          <Text style={ui.meta}>
            Das Gerät sperrt sich nicht, solange die Spieltafel offen ist. Braucht etwas mehr Akku.
          </Text>
        </View>
        <Switch
          value={keepScreenOn}
          onValueChange={setKeepScreenOn}
          trackColor={{ false: Colors.border, true: Colors.primaryLight }}
          thumbColor={keepScreenOn ? Colors.primary : '#f4f3f4'}
          accessibilityLabel="Bildschirm anlassen"
        />
      </Card>
    </Screen>
  );
}

const sBase = StyleSheet.create({
  text: { flex: 1, gap: 3 },
});
