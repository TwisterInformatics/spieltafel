import { router } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, TextInput, View } from 'react-native';

import { Card, GameIcon, Screen, Title, styles as ui } from '@/components/ui';
import { Colors, TemplateColors } from '@/constants/theme';
import { useGames } from '@/data/hooks';
import { participantsLine, shortScore, winnerText } from '@/domain/summary';
import { getTemplate } from '@/domain/templates';
import type { Game } from '@/domain/types';

function dayLabel(ts: number): string {
  const d = new Date(ts);
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);
  if (d.toDateString() === today.toDateString()) return 'Heute';
  if (d.toDateString() === yesterday.toDateString()) return 'Gestern';
  return d.toLocaleDateString('de-CH', { weekday: 'long', day: 'numeric', month: 'long' });
}

export default function HistoryScreen() {
  const games = useGames();
  const [query, setQuery] = useState('');
  if (!games) return null;

  const q = query.trim().toLowerCase();
  const visible = games.filter((g) => {
    if (!q) return true;
    const name = getTemplate(g.templateId)?.name ?? '';
    return [name, participantsLine(g)].some((s) => s.toLowerCase().includes(q));
  });

  const groups: { day: string; games: Game[] }[] = [];
  for (const g of visible) {
    const day = dayLabel(g.createdAt);
    const last = groups[groups.length - 1];
    if (last && last.day === day) last.games.push(g);
    else groups.push({ day, games: [g] });
  }

  return (
    <Screen>
      <Title sub="Alle gespielten Partien">Verlauf</Title>
      <TextInput
        value={query}
        onChangeText={setQuery}
        placeholder="Partien durchsuchen"
        placeholderTextColor={Colors.faint}
        style={s.search}
        autoCorrect={false}
      />
      {games.length === 0 ? <Text style={ui.meta}>Noch keine Partien. Startet eine über die Spielsuche.</Text> : null}
      {groups.map((grp) => (
        <View key={grp.day} style={{ gap: 12 }}>
          <Text style={s.day}>{grp.day}</Text>
          {grp.games.map((g) => {
            const t = getTemplate(g.templateId);
            const status = g.finishedAt === null ? `Läuft · ${shortScore(g)}` : winnerText(g);
            return (
              <Card key={g.id} onPress={() => router.push(`/partie/${g.id}`)}>
                <GameIcon letter={g.templateId === 'frei' ? '+' : (t?.name ?? '?').charAt(0)} color={TemplateColors[g.templateId] ?? Colors.primary} />
                <View style={{ flex: 1, gap: 3 }}>
                  <Text style={ui.cardTitle}>{t?.name ?? 'Spiel'}</Text>
                  <Text style={ui.meta} numberOfLines={1}>
                    {status}
                  </Text>
                </View>
                <Text style={{ color: '#b4b9b5', fontSize: 22 }}>›</Text>
              </Card>
            );
          })}
        </View>
      ))}
    </Screen>
  );
}

const s = StyleSheet.create({
  search: { height: 48, borderRadius: 14, backgroundColor: Colors.surface, paddingHorizontal: 16, fontSize: 16, color: Colors.ink },
  day: { fontSize: 13, fontWeight: '700', color: Colors.muted, marginTop: 6 },
});
