import { router } from 'expo-router';
import { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Card, Chip, GameIcon, Label, useContentWidth, useUi } from '@/components/ui';
import { useLayout, useScaledStyles } from '@/constants/layout';
import { Colors, TemplateColors } from '@/constants/theme';
import { useGames } from '@/data/hooks';
import { dayLabel, participantsLine, shortScore, winnerText } from '@/domain/summary';
import { getTemplate, searchTemplates } from '@/domain/templates';
import type { Game, GameTemplate } from '@/domain/types';

const CATEGORIES: (GameTemplate['category'] | undefined)[] = [undefined, 'Kartenspiel', 'Würfelspiel', 'Legespiel', 'Frei'];
/** So viele beendete Partien zeigt die Seitenspalte auf breiten Tablets. */
const RECENT_COUNT = 4;

export default function SearchScreen() {
  const ui = useUi();
  const width = useContentWidth();
  const s = useScaledStyles(sBase);
  const { wide } = useLayout();
  const [query, setQuery] = useState('');
  const [category, setCategory] = useState<GameTemplate['category'] | undefined>();
  const games = useGames();
  const running = (games ?? []).filter((g) => g.finishedAt === null);
  const recent = (games ?? []).filter((g) => g.finishedAt !== null).slice(0, RECENT_COUNT);
  const results = searchTemplates(query, category);

  const title = (
    <View style={{ flex: 1 }}>
      <Text style={s.appTitle}>Spieltafel</Text>
      <Text style={ui.sub}>Welches Spiel spielt ihr heute?</Text>
    </View>
  );
  const search = (
    <TextInput
      value={query}
      onChangeText={setQuery}
      placeholder="Spiel suchen, z.B. Jass"
      placeholderTextColor={Colors.faint}
      style={[s.search, wide && s.searchWide]}
      clearButtonMode="while-editing"
      autoCorrect={false}
    />
  );
  const chips = (
    <View style={ui.wrap}>
      {CATEGORIES.map((c) => (
        <Chip key={c ?? 'alle'} title={c ?? 'Alle'} active={category === c} onPress={() => setCategory(c)} />
      ))}
    </View>
  );

  if (wide) {
    // Tablet quer: links die Spiele als Kacheln, rechts laufende und letzte Partien.
    return (
      <SafeAreaView style={ui.screen} edges={['top', 'left', 'right']}>
        <View style={s.wideRow}>
          <ScrollView style={{ flex: 1 }} contentContainerStyle={s.wideMain} keyboardShouldPersistTaps="handled">
            <View style={s.header}>{title}</View>
            <View style={s.searchRow}>
              {search}
              {chips}
            </View>
            <Label>Spiele</Label>
            <Templates results={results} />
          </ScrollView>
          <ScrollView style={s.side} contentContainerStyle={s.sideContent}>
            <HeaderButtons />
            {running.map((g) => (
              <LiveGame key={g.id} game={g} />
            ))}
            {recent.length > 0 ? <Label>Zuletzt gespielt</Label> : null}
            {recent.length > 0 ? <RecentGames games={recent} /> : null}
          </ScrollView>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={ui.screen} edges={['top', 'left', 'right']}>
      <ScrollView contentContainerStyle={[ui.content, width]} keyboardShouldPersistTaps="handled">
        <View style={s.header}>
          {title}
          <HeaderButtons />
        </View>
        {search}
        {chips}
        {running.map((g) => (
          <LiveGame key={g.id} game={g} />
        ))}
        <Label>Spiele</Label>
        <Templates results={results} />
      </ScrollView>
    </SafeAreaView>
  );
}

function HeaderButtons() {
  const s = useScaledStyles(sBase);
  return (
    <View style={s.headerBtns}>
      <Pressable onPress={() => router.push('/verlauf')} style={s.historyBtn} accessibilityRole="button">
        <Text style={s.historyText}>Verlauf</Text>
      </Pressable>
      <Pressable
        onPress={() => router.push('/einstellungen')}
        style={s.settingsBtn}
        accessibilityRole="button"
        accessibilityLabel="Einstellungen">
        {/* U+FE0E erzwingt das Textzeichen; iOS zeigt sonst ein buntes Emoji statt des grünen Zahnrads. */}
        <Text style={s.settingsIcon}>{'\u2699\uFE0E'}</Text>
      </Pressable>
    </View>
  );
}

function LiveGame({ game }: { game: Game }) {
  const s = useScaledStyles(sBase);
  return (
    <Pressable onPress={() => router.push(`/partie/${game.id}`)} style={s.live}>
      <Text style={s.liveMeta}>Laufende Partie · {getTemplate(game.templateId)?.name ?? 'Spiel'}</Text>
      <View style={s.liveRow}>
        <Text style={s.liveScore}>{shortScore(game)}</Text>
        <View style={s.pill}>
          <Text style={s.pillText}>Fortsetzen</Text>
        </View>
      </View>
      <Text style={s.liveMeta} numberOfLines={1}>
        {participantsLine(game)}
      </Text>
    </Pressable>
  );
}

/** Spielvorlagen: Liste auf Handys, Kachelraster auf Tablets. */
function Templates({ results }: { results: GameTemplate[] }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const { gridColumns } = useLayout();
  const [gridWidth, setGridWidth] = useState(0);

  if (results.length === 0) {
    return <Text style={ui.meta}>Kein Spiel gefunden. Mit der freien Zählung geht jedes Spiel.</Text>;
  }
  const icon = (t: GameTemplate) => (
    <GameIcon letter={t.id === 'frei' ? '+' : t.name.charAt(0)} color={TemplateColors[t.id] ?? Colors.primary} />
  );

  if (gridColumns === 1) {
    return (
      <>
        {results.map((t) => (
          <Card key={t.id} onPress={() => router.push(`/neu/${t.id}`)}>
            {icon(t)}
            <View style={{ flex: 1, gap: 3 }}>
              <Text style={ui.cardTitle}>{t.name}</Text>
              <Text style={ui.meta}>{t.description}</Text>
            </View>
            <Text style={s.chev}>›</Text>
          </Card>
        ))}
      </>
    );
  }

  const gap = s.grid.gap as number;
  const tileWidth = gridWidth > 0 ? (gridWidth - gap * (gridColumns - 1)) / gridColumns : undefined;
  return (
    <View style={s.grid} onLayout={(e) => setGridWidth(e.nativeEvent.layout.width)}>
      {tileWidth
        ? results.map((t) => (
            <Card key={t.id} onPress={() => router.push(`/neu/${t.id}`)} style={{ ...s.tile, width: tileWidth }}>
              {icon(t)}
              <View style={{ gap: 3 }}>
                <Text style={ui.cardTitle}>{t.name}</Text>
                <Text style={ui.meta}>{t.description}</Text>
              </View>
            </Card>
          ))
        : null}
    </View>
  );
}

function RecentGames({ games }: { games: Game[] }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  return (
    <View style={s.recent}>
      {games.map((g, i) => {
        const t = getTemplate(g.templateId);
        return (
          <Pressable key={g.id} onPress={() => router.push(`/partie/${g.id}`)} style={[s.recentRow, i > 0 && s.recentDivider]}>
            <View style={s.smallIcon}>
              <GameIcon letter={g.templateId === 'frei' ? '+' : (t?.name ?? '?').charAt(0)} color={TemplateColors[g.templateId] ?? Colors.primary} />
            </View>
            <View style={{ flex: 1, gap: 2 }}>
              <Text style={ui.cardTitle} numberOfLines={1}>
                {t?.name ?? 'Spiel'}
              </Text>
              <Text style={ui.meta} numberOfLines={1}>
                {`${dayLabel(g.createdAt)} · ${winnerText(g)}`}
              </Text>
            </View>
          </Pressable>
        );
      })}
    </View>
  );
}

const sBase = StyleSheet.create({
  header: { flexDirection: 'row', alignItems: 'center', marginTop: 8 },
  appTitle: { fontSize: 30, fontWeight: '800', color: Colors.ink, letterSpacing: -0.5 },
  historyBtn: { paddingVertical: 8, paddingHorizontal: 14, borderRadius: 20, backgroundColor: Colors.surface },
  historyText: { color: Colors.primary, fontWeight: '700' },
  headerBtns: { flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end', gap: 8 },
  settingsBtn: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: Colors.surface,
    alignItems: 'center',
    justifyContent: 'center',
  },
  settingsIcon: { color: Colors.primary, fontSize: 20 },
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
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  tile: { flexDirection: 'column', alignItems: 'flex-start', paddingVertical: 16, gap: 10 },
  wideRow: { flex: 1, flexDirection: 'row', paddingHorizontal: 20, gap: 20 },
  wideMain: { gap: 12, paddingTop: 4, paddingBottom: 24 },
  // Mit Umbruch: Passen Suchfeld und Filter nicht in eine Zeile (iPad hochkant), rutschen die Filter darunter.
  searchRow: { flexDirection: 'row', flexWrap: 'wrap', alignItems: 'center', gap: 12 },
  searchWide: { flexGrow: 1, flexBasis: 260 },
  side: { width: 280, flexGrow: 0 },
  sideContent: { gap: 12, paddingTop: 8, paddingBottom: 24 },
  recent: { backgroundColor: Colors.surface, borderRadius: 16, paddingVertical: 4 },
  recentRow: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 10, paddingHorizontal: 14 },
  recentDivider: { borderTopWidth: 1, borderTopColor: '#f0ede5' },
  smallIcon: { transform: [{ scale: 0.75 }], marginHorizontal: -6 },
});
