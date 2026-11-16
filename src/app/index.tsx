import { router } from 'expo-router';
import { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Card, Chip, GameIcon, Label, useContentWidth, useUi } from '@/components/ui';
import { useScaledStyles } from '@/constants/layout';
import { Colors, TemplateColors } from '@/constants/theme';
import { useGames } from '@/data/hooks';
import { participantsLine, shortScore } from '@/domain/summary';
import { getTemplate, searchTemplates } from '@/domain/templates';
import type { GameTemplate } from '@/domain/types';

const CATEGORIES: (GameTemplate['category'] | undefined)[] = [undefined, 'Kartenspiel', 'Legespiel', 'Frei'];

export default function SearchScreen() {
  const ui = useUi();
  const width = useContentWidth();
  const s = useScaledStyles(sBase);
  const [query, setQuery] = useState('');
  const [category, setCategory] = useState<GameTemplate['category'] | undefined>();
  const games = useGames();
  const running = (games ?? []).filter((g) => g.finishedAt === null);
  const results = searchTemplates(query, category);

  return (
    <SafeAreaView style={ui.screen} edges={['top', 'left', 'right']}>
      <ScrollView contentContainerStyle={[ui.content, width]} keyboardShouldPersistTaps="handled">
        <View style={s.header}>
          <View style={{ flex: 1 }}>
            <Text style={s.appTitle}>Spieltafel</Text>
            <Text style={ui.sub}>Welches Spiel spielt ihr heute?</Text>
          </View>
          <Pressable onPress={() => router.push('/verlauf')} style={s.historyBtn} accessibilityRole="button">
            <Text style={s.historyText}>Verlauf</Text>
          </Pressable>
        </View>

        <TextInput
          value={query}
          onChangeText={setQuery}
          placeholder="Spiel suchen, z.B. Jass"
          placeholderTextColor={Colors.faint}
          style={s.search}
          clearButtonMode="while-editing"
          autoCorrect={false}
        />
        <View style={ui.wrap}>
          {CATEGORIES.map((c) => (
            <Chip key={c ?? 'alle'} title={c ?? 'Alle'} active={category === c} onPress={() => setCategory(c)} />
          ))}
        </View>

        {running.map((g) => (
          <Pressable key={g.id} onPress={() => router.push(`/partie/${g.id}`)} style={s.live}>
            <Text style={s.liveMeta}>Laufende Partie · {getTemplate(g.templateId)?.name ?? 'Spiel'}</Text>
            <View style={s.liveRow}>
              <Text style={s.liveScore}>{shortScore(g)}</Text>
              <View style={s.pill}>
                <Text style={s.pillText}>Fortsetzen</Text>
              </View>
            </View>
            <Text style={s.liveMeta} numberOfLines={1}>
              {participantsLine(g)}
            </Text>
          </Pressable>
        ))}

        <Label>Spiele</Label>
        {results.length === 0 ? (
          <Text style={ui.meta}>Kein Spiel gefunden. Mit der freien Zählung geht jedes Spiel.</Text>
        ) : null}
        {results.map((t) => (
          <Card key={t.id} onPress={() => router.push(`/neu/${t.id}`)}>
            <GameIcon letter={t.id === 'frei' ? '+' : t.name.charAt(0)} color={TemplateColors[t.id] ?? Colors.primary} />
            <View style={{ flex: 1, gap: 3 }}>
              <Text style={ui.cardTitle}>{t.name}</Text>
              <Text style={ui.meta}>{t.description}</Text>
            </View>
            <Text style={s.chev}>›</Text>
          </Card>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

const sBase = StyleSheet.create({
  header: { flexDirection: 'row', alignItems: 'center', marginTop: 8 },
  appTitle: { fontSize: 30, fontWeight: '800', color: Colors.ink, letterSpacing: -0.5 },
  historyBtn: { paddingVertical: 8, paddingHorizontal: 14, borderRadius: 20, backgroundColor: Colors.surface },
  historyText: { color: Colors.primary, fontWeight: '700' },
  search: {
    height: 48,
    borderRadius: 14,
    backgroundColor: Colors.surface,
    paddingHorizontal: 16,
    fontSize: 16,
    color: Colors.ink,
  },
  live: { backgroundColor: Colors.primary, borderRadius: 18, padding: 16, gap: 10 },
  liveMeta: { color: Colors.primaryLight, fontSize: 13 },
  liveRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  liveScore: { color: '#fff', fontSize: 24, fontWeight: '800', flexShrink: 1 },
  pill: { backgroundColor: Colors.gold, borderRadius: 12, paddingVertical: 8, paddingHorizontal: 14 },
  pillText: { fontWeight: '700', color: Colors.ink },
  chev: { color: '#b4b9b5', fontSize: 22 },
});
