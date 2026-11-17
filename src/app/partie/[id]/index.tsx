import { router, Stack, useLocalSearchParams } from 'expo-router';
import { useSQLiteContext } from 'expo-sqlite';
import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Avatar, Button, Card, Label, Screen, Title, useUi } from '@/components/ui';
import { useLayout, useScaledStyles } from '@/constants/layout';
import { Colors } from '@/constants/theme';
import { createGame, deleteLastRound, finishGame } from '@/data/db';
import { useGame } from '@/data/hooks';
import { ranking, scoreTable, totals } from '@/domain/scoring';
import { winnerText } from '@/domain/summary';
import { getTemplate } from '@/domain/templates';
import type { Game } from '@/domain/types';

export default function ScoreboardScreen() {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const { wide } = useLayout();
  const { id } = useLocalSearchParams<{ id: string }>();
  const db = useSQLiteContext();
  const [game, reload] = useGame(id);

  if (game === undefined) return null;
  if (game === null) return <Text style={{ padding: 20 }}>Partie nicht gefunden.</Text>;

  const template = getTemplate(game.templateId);
  const finished = game.finishedAt !== null;

  async function endGame() {
    await finishGame(db, game!.id);
    await reload();
  }

  function confirmUndo() {
    Alert.alert('Letzte Runde löschen?', 'Die Punkte der letzten Runde werden entfernt.', [
      { text: 'Abbrechen', style: 'cancel' },
      {
        text: 'Löschen',
        style: 'destructive',
        onPress: async () => {
          await deleteLastRound(db, game!.id);
          await reload();
        },
      },
    ]);
  }

  async function rematch() {
    const newId = await createGame(db, game!.templateId, game!.participants, game!.settings);
    router.replace(`/partie/${newId}`);
  }

  const footer = finished ? (
    <>
      <Button title="Revanche" onPress={rematch} />
      <Button title="Fertig" variant="secondary" onPress={() => router.dismissTo('/')} />
    </>
  ) : (
    <>
      <Button title={game.rounds.length ? 'Nächste Runde erfassen' : 'Erste Runde erfassen'} onPress={() => router.push(`/partie/${game.id}/runde`)} />
      <Button title="Partie beenden" variant="secondary" onPress={endGame} disabled={game.rounds.length === 0} />
    </>
  );

  const undo =
    game.rounds.length > 0 ? (
      <Pressable onPress={confirmUndo} style={s.undo}>
        <Text style={s.undoText}>Letzte Runde löschen</Text>
      </Pressable>
    ) : null;

  if (wide) {
    // Tablet quer: links Stand, Rangliste und Aktionen, rechts die ganze Rundentabelle.
    return (
      <SafeAreaView style={ui.screen} edges={['bottom', 'left', 'right']}>
        <Stack.Screen options={{ title: template?.name ?? 'Spielstand' }} />
        <View style={s.wideRow}>
          <View style={s.wideSide}>
            <ScrollView style={{ flex: 1 }} contentContainerStyle={s.wideSideContent}>
              {finished ? <Winner game={game} /> : <Title sub={statusLine(game)}>Spielstand</Title>}
              {!finished && game.rounds.length > 0 ? <Progress game={game} /> : null}
              {game.rounds.length > 0 ? <Ranking game={game} compact /> : null}
            </ScrollView>
            <View style={s.wideActions}>{footer}</View>
          </View>
          <View style={{ flex: 1 }}>
            <Label>Runden</Label>
            <ScrollView style={{ flex: 1 }} contentContainerStyle={s.wideTable}>
              {game.rounds.length > 0 ? <ScoreTable game={game} /> : <Text style={ui.meta}>Noch keine Runde gespielt.</Text>}
              {undo}
            </ScrollView>
          </View>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <Screen footer={footer}>
      <Stack.Screen options={{ title: template?.name ?? 'Spielstand' }} />
      {finished ? <Winner game={game} /> : <Title sub={statusLine(game)}>Spielstand</Title>}
      {finished ? <Ranking game={game} /> : null}
      {game.rounds.length > 0 ? <ScoreTable game={game} /> : <Text style={ui.meta}>Noch keine Runde gespielt.</Text>}
      {!finished && game.rounds.length > 0 ? <Progress game={game} /> : null}
      {undo}
    </Screen>
  );
}

/** Teams heissen "Anna und Ben" und brauchen den Plural. */
function verb(name: string, singular: string, plural: string): string {
  return name.includes(' und ') ? plural : singular;
}

function statusLine(game: Game): string {
  const n = game.rounds.length;
  const end = game.settings.end;
  const base = n === 1 ? 'nach 1 Runde' : `nach ${n} Runden`;
  if (end.type === 'target') return `${base} · Ziel ${end.target}`;
  if (end.type === 'rounds') return `${base} von ${end.rounds}`;
  return base;
}

function roundLabel(game: Game, i: number): string {
  const r = game.rounds[i];
  if (r.kind === 'coiffeur') return r.modeName;
  if (r.kind === 'jassSchieber') return `${i + 1} ${r.modeName}`;
  return String(i + 1);
}

function ScoreTable({ game }: { game: Game }) {
  const s = useScaledStyles(sBase);
  const rows = scoreTable(game);
  const t = totals(game);
  const many = game.participants.length > 4;
  const colW = many ? 72 : undefined;
  const cell = (key: string, text: string, style?: object) => (
    <Text key={key} style={[s.cell, colW ? { width: colW } : { flex: 1 }, style]} numberOfLines={1}>
      {text}
    </Text>
  );
  const table = (
    <View style={s.table}>
      <View style={[s.tr, s.head]}>
        {cell('h', 'Runde', [s.first, s.headText])}
        {game.participants.map((p) => cell(p.id, p.name, s.headText))}
      </View>
      {rows.map((row, i) => (
        <View key={i} style={s.tr}>
          {cell('l', roundLabel(game, i), s.first)}
          {game.participants.map((p) => cell(p.id, String(row[p.id] ?? 0)))}
        </View>
      ))}
      <View style={[s.tr, s.total]}>
        {cell('t', 'Total', [s.first, s.totalText])}
        {game.participants.map((p) => cell(p.id, String(t[p.id]), s.totalText))}
      </View>
    </View>
  );
  return many ? <ScrollView horizontal>{table}</ScrollView> : table;
}

function Progress({ game }: { game: Game }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const end = game.settings.end;
  const leader = ranking(game)[0];
  if (end.type === 'target') {
    const t = totals(game);
    const top = game.participants.reduce((a, b) => (t[a.id] >= t[b.id] ? a : b));
    const pct = Math.min(1, t[top.id] / end.target);
    return (
      <Card style={{ flexDirection: 'column', alignItems: 'stretch', gap: 8 }}>
        <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
          <Text style={ui.cardTitle}>{`${top.name} ${verb(top.name, 'ist', 'sind')} am nächsten an ${end.target}`}</Text>
          <Text style={s.progressValue}>{t[top.id]}</Text>
        </View>
        <View style={s.bar}>
          <View style={[s.barFill, { width: `${pct * 100}%` }]} />
        </View>
        <Text style={ui.meta}>{`${leader.participant.name} ${verb(leader.participant.name, 'führt', 'führen')} mit ${leader.total} Punkten`}</Text>
      </Card>
    );
  }
  return (
    <Card>
      <Text style={ui.cardTitle}>{`${leader.participant.name} ${verb(leader.participant.name, 'führt', 'führen')} mit ${leader.total} Punkten`}</Text>
    </Card>
  );
}

function Winner({ game }: { game: Game }) {
  const s = useScaledStyles(sBase);
  const top = ranking(game)[0];
  return (
    <View style={s.winner}>
      <View style={s.crown}>
        <Text style={s.crownText}>{top.participant.name.charAt(0)}</Text>
      </View>
      <Text style={s.winnerText}>{winnerText(game)}</Text>
      <Text style={s.winnerSub}>{`mit ${top.total} Punkten`}</Text>
    </View>
  );
}

function Ranking({ game, compact }: { game: Game; compact?: boolean }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  if (compact) {
    // Tablet: alle Plätze als Zeilen in einer Karte, damit die ganze Rangliste sichtbar bleibt
    return (
      <View style={s.rankList}>
        {ranking(game).map((e, i) => (
          <View key={e.participant.id} style={[s.rankRow, i > 0 && s.rankDivider]}>
            <Text style={s.rank}>{e.rank}</Text>
            <Avatar name={e.participant.name} color={e.participant.color} size={28} />
            <Text style={[ui.cardTitle, { flex: 1 }]} numberOfLines={1}>
              {e.participant.name}
            </Text>
            <Text style={s.rankTotal}>{e.total}</Text>
          </View>
        ))}
      </View>
    );
  }
  return (
    <>
      {ranking(game).map((e) => (
        <Card key={e.participant.id}>
          <Text style={s.rank}>{e.rank}</Text>
          <Avatar name={e.participant.name} color={e.participant.color} />
          <Text style={[ui.cardTitle, { flex: 1 }]}>{e.participant.name}</Text>
          <Text style={s.rankTotal}>{e.total}</Text>
        </Card>
      ))}
    </>
  );
}

const sBase = StyleSheet.create({
  table: { backgroundColor: Colors.surface, borderRadius: 16, overflow: 'hidden' },
  tr: { flexDirection: 'row', borderTopWidth: 1, borderTopColor: '#f0ede5', paddingHorizontal: 8 },
  head: { backgroundColor: '#faf8f3', borderTopWidth: 0 },
  total: { backgroundColor: '#eef5f0' },
  cell: { paddingVertical: 10, paddingHorizontal: 4, textAlign: 'center', fontSize: 15, color: Colors.ink },
  first: { textAlign: 'left', color: Colors.muted, minWidth: 64 },
  headText: { fontSize: 12, fontWeight: '700', color: Colors.muted },
  totalText: { fontSize: 17, fontWeight: '800', color: Colors.ink },
  bar: { height: 10, borderRadius: 5, backgroundColor: '#e6e2d8', overflow: 'hidden' },
  barFill: { height: 10, backgroundColor: Colors.danger },
  winner: { backgroundColor: Colors.primary, borderRadius: 22, paddingVertical: 28, paddingHorizontal: 20, alignItems: 'center', gap: 10 },
  progressValue: { fontWeight: '700', color: Colors.primary, fontSize: 16 },
  winnerSub: { color: Colors.primaryLight, fontSize: 14 },
  crownText: { fontSize: 30, fontWeight: '800', color: Colors.ink },
  winnerText: { fontSize: 26, fontWeight: '800', color: '#fff', textAlign: 'center' },
  rank: { width: 20, fontWeight: '800', color: Colors.muted, fontSize: 16 },
  rankTotal: { fontWeight: '800', fontSize: 18, color: Colors.ink },
  undo: { alignSelf: 'center', padding: 8 },
  undoText: { color: Colors.danger, fontWeight: '600', fontSize: 15 },
  rankList: { backgroundColor: Colors.surface, borderRadius: 16, paddingVertical: 4 },
  rankRow: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 6, paddingHorizontal: 16 },
  rankDivider: { borderTopWidth: 1, borderTopColor: '#f0ede5' },
  wideRow: { flex: 1, flexDirection: 'row', gap: 20, paddingHorizontal: 20, paddingTop: 4, paddingBottom: 12 },
  wideSide: { width: 310 },
  wideSideContent: { gap: 12, paddingBottom: 12 },
  wideActions: { gap: 10, paddingTop: 8 },
  wideTable: { paddingTop: 12, paddingBottom: 12, gap: 12 },
  crown: { width: 72, height: 72, borderRadius: 36, backgroundColor: Colors.gold, alignItems: 'center', justifyContent: 'center' },
});
