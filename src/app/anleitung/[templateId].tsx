import { Stack, useLocalSearchParams } from 'expo-router';
import { Linking, StyleSheet, Text, View } from 'react-native';

import { Button, Card, Label, Screen, Title, useUi } from '@/components/ui';
import { useScaledStyles } from '@/constants/layout';
import { Colors } from '@/constants/theme';
import { getTemplate } from '@/domain/templates';

export default function GuideScreen() {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const { templateId } = useLocalSearchParams<{ templateId: string }>();
  const template = getTemplate(templateId);
  if (!template) return <Text style={{ padding: 20 }}>Spiel nicht gefunden.</Text>;
  const { guide } = template;
  const link = guide.link;

  return (
    <Screen
      footer={
        link ? <Button title="Ausführliche Anleitung öffnen" variant="secondary" onPress={() => Linking.openURL(link.url)} /> : undefined
      }>
      <Stack.Screen options={{ title: 'Kurzanleitung' }} />
      <Title sub={template.description}>{template.name}</Title>
      <Card>
        <Text style={s.goal}>{guide.goal}</Text>
      </Card>
      <Label>So geht's</Label>
      <Card style={s.list}>
        {guide.steps.map((step, i) => (
          <View key={i} style={s.item}>
            <Text style={s.no}>{i + 1}</Text>
            <Text style={s.text}>{step}</Text>
          </View>
        ))}
      </Card>
      <Label>Punkte und Spielende</Label>
      <Card style={s.list}>
        {guide.scoring.map((line, i) => (
          <View key={i} style={s.item}>
            <Text style={s.dot}>•</Text>
            <Text style={s.text}>{line}</Text>
          </View>
        ))}
      </Card>
      {link ? <Text style={ui.meta}>{`Quelle: ${link.title}. Viele Runden spielen eigene Hausregeln.`}</Text> : null}
    </Screen>
  );
}

const sBase = StyleSheet.create({
  goal: { flex: 1, fontSize: 17, fontWeight: '600', color: Colors.ink, lineHeight: 24 },
  list: { flexDirection: 'column', alignItems: 'stretch', gap: 12 },
  item: { flexDirection: 'row', gap: 12, alignItems: 'flex-start' },
  no: { width: 22, fontSize: 15, fontWeight: '800', color: Colors.primary, lineHeight: 22 },
  dot: { width: 22, fontSize: 15, fontWeight: '800', color: Colors.primary, lineHeight: 22, textAlign: 'center' },
  text: { flex: 1, fontSize: 15, color: Colors.ink, lineHeight: 22 },
});
