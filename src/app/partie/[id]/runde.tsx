import { router, Stack, useLocalSearchParams } from 'expo-router';
import { useSQLiteContext } from 'expo-sqlite';
import { useState, type ReactNode } from 'react';
import { Pressable, StyleSheet, Text, View, type ViewStyle } from 'react-native';

import { Avatar, Button, Card, Chip, Hint, Label, NumberField, Screen, useUi } from '@/components/ui';
import { useLayout, useScaledStyles } from '@/constants/layout';
import { Colors } from '@/constants/theme';
import { addRound, finishGame, getGame } from '@/data/db';
import { useGame } from '@/data/hooks';
import {
  alive,
  endsWithLastRound,
  JASS_MATCH,
  jassCounterPoints,
  openCoiffeurModes,
  placementPoints,
  roundScores,
  skyjoRoundScores,
  totals,
  wizardPoints,
} from '@/domain/scoring';
import { getTemplate } from '@/domain/templates';
import type { Game, Participant, RoundData } from '@/domain/types';

type Nums = Record<string, number | null>;
const empty = (ps: Participant[]): Nums => Object.fromEntries(ps.map((p) => [p.id, null]));
const filled = (n: Nums): Record<string, number> => Object.fromEntries(Object.entries(n).map(([k, v]) => [k, v ?? 0]));

export default function RoundScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const [game] = useGame(id);
  if (!game) return null;
  return <RoundForm game={game} />;
}

function RoundForm({ game }: { game: Game }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const db = useSQLiteContext();
  const { wide } = useLayout();
  const templateKind = getTemplate(game.templateId)?.kind ?? 'rounds';
  // Partien, die vor der Umstellung auf Leben gestartet wurden, zählen weiter normale Punkte.
  const kind = templateKind === 'lives' && game.settings.end.type !== 'lastStanding' ? 'rounds' : templateKind;
  const ps = game.participants;
  const [saving, setSaving] = useState(false);

  // Gemeinsamer Zustand für alle Formulare
  const [scores, setScores] = useState<Nums>(() => empty(ps));
  const [second, setSecond] = useState<Nums>(() => empty(ps));
  const [finisher, setFinisher] = useState<string | null>(null);
  const [modeName, setModeName] = useState<string | null>(null);
  const [teamId, setTeamId] = useState<string>(ps[0]?.id);
  const [stoeck, setStoeck] = useState<string | null>(null);
  const [order, setOrder] = useState<string[]>([]);
  const [losers, setLosers] = useState<string[]>([]);

  const set = (setter: typeof setScores) => (pid: string, v: number | null) => setter((prev) => ({ ...prev, [pid]: v }));
  const modes = game.settings.modes ?? [];
  const mode = modes.find((m) => m.name === modeName);
  const anyValue = Object.values(scores).some((v) => v !== null);

  let data: RoundData | null = null;
  let hint: string | null = null;

  if (kind === 'rounds') {
    if (anyValue) data = { kind: 'rounds', scores: filled(scores) };
  } else if (kind === 'skyjo') {
    if (anyValue) {
      data = { kind: 'skyjo', scores: filled(scores), finisherId: finisher };
      if (finisher) {
        const raw = filled(scores);
        const adj = skyjoRoundScores(raw, finisher);
        if (adj[finisher] !== raw[finisher]) {
          const name = ps.find((p) => p.id === finisher)?.name;
          hint = `${name} hat nicht allein die tiefste Punktzahl. Die ${raw[finisher]} Punkte werden verdoppelt und als ${adj[finisher]} gezählt.`;
        }
      }
    }
  } else if (kind === 'jassSchieber') {
    const [a, b] = ps;
    const own = scores[a.id];
    if (mode && own !== null) {
      const counter = jassCounterPoints(own);
      const weis = filled(second);
      const st: Record<string, number> = { [a.id]: stoeck === a.id ? 20 : 0, [b.id]: stoeck === b.id ? 20 : 0 };
      data = { kind: 'jassSchieber', factor: mode.factor, modeName: mode.name, stich: { [a.id]: own, [b.id]: counter }, weis, stoeck: st };
    }
  } else if (kind === 'differenzler') {
    const complete = ps.every((p) => scores[p.id] !== null && second[p.id] !== null);
    if (complete) data = { kind: 'differenzler', ansage: filled(scores), erzielt: filled(second) };
    const sum = ps.reduce((acc, p) => acc + (second[p.id] ?? 0), 0);
    if (ps.every((p) => second[p.id] !== null) && sum !== 157) hint = `Die erzielten Punkte ergeben ${sum}. Normal sind es zusammen 157.`;
  } else if (kind === 'placement') {
    if (ps.length > 1 && order.length >= ps.length - 1) data = { kind: 'placement', order };
  } else if (kind === 'lives') {
    if (losers.length > 0) data = { kind: 'lives', losers };
  } else if (kind === 'wizard') {
    const complete = ps.every((p) => scores[p.id] !== null && second[p.id] !== null);
    if (complete) data = { kind: 'wizard', ansage: filled(scores), stiche: filled(second) };
    const sum = ps.reduce((acc, p) => acc + (second[p.id] ?? 0), 0);
    const cards = game.rounds.length + 1;
    if (ps.every((p) => second[p.id] !== null) && sum !== cards)
      hint = `Die Stiche ergeben ${sum}. In Runde ${cards} gibt es ${cards} ${cards === 1 ? 'Stich' : 'Stiche'}.`;
  } else if (kind === 'coiffeur') {
    const pts = scores[teamId];
    if (modeName && pts !== null) data = { kind: 'coiffeur', teamId, modeName, points: pts };
  }

  async function save() {
    if (!data) return;
    setSaving(true);
    await addRound(db, game.id, data);
    const updated = await getGame(db, game.id);
    if (updated && endsWithLastRound(updated)) await finishGame(db, game.id);
    router.back();
  }

  const nextNo = game.rounds.length + 1;
  const title = `Runde ${nextNo} erfassen`;

  const form = (
    <Screen footer={<Button title="Runde speichern" onPress={save} disabled={!data || saving} />}>
      <Stack.Screen options={{ title }} />

      {kind === 'rounds' || kind === 'skyjo' ? (
        <View style={wide ? s.playerGrid : s.playerList}>
          {ps.map((p) => (
            <PlayerRow
              key={p.id}
              p={p}
              meta={`Total ${totals(game)[p.id]}${finisher === p.id ? ' · hat beendet' : ''}`}
              style={wide ? s.playerCell : undefined}>
              {kind === 'rounds' ? <Stepper value={scores[p.id]} onChange={(v) => set(setScores)(p.id, v)} /> : null}
              {kind === 'skyjo' ? <NumberField value={scores[p.id]} onChange={(v) => set(setScores)(p.id, v)} /> : null}
            </PlayerRow>
          ))}
        </View>
      ) : null}

      {kind === 'skyjo' ? (
        <>
          <Label>Wer hat die Runde beendet?</Label>
          <View style={ui.wrap}>
            {ps.map((p) => (
              <Chip key={p.id} title={p.name} active={finisher === p.id} onPress={() => setFinisher(finisher === p.id ? null : p.id)} />
            ))}
          </View>
        </>
      ) : null}

      {kind === 'jassSchieber' ? (
        <>
          <Label>Trumpf</Label>
          <View style={ui.wrap}>
            {modes.map((m) => (
              <Chip key={m.name} title={`${m.name} ×${m.factor}`} active={modeName === m.name} onPress={() => setModeName(m.name)} />
            ))}
          </View>
          <Label>Stichpunkte</Label>
          <View style={{ flexDirection: 'row', gap: 12 }}>
            <Card style={s.team}>
              <Text style={s.teamName}>{ps[0].name}</Text>
              <NumberField value={scores[ps[0].id]} onChange={(v) => set(setScores)(ps[0].id, v)} width={120} allowNegative={false} />
              <Pressable onPress={() => set(setScores)(ps[0].id, JASS_MATCH)}>
                <Text style={s.link}>Match 257</Text>
              </Pressable>
            </Card>
            <Card style={s.team}>
              <Text style={s.teamName}>{ps[1].name}</Text>
              <View style={[ui.number as object, s.auto]}>
                <Text style={s.autoValue}>{scores[ps[0].id] === null ? '–' : jassCounterPoints(scores[ps[0].id]!)}</Text>
              </View>
              <Text style={s.autoNote}>automatisch: 157 − eigene</Text>
            </Card>
          </View>
          <Label>Weis</Label>
          {ps.map((p) => (
            <PlayerRow key={p.id} p={p}>
              <NumberField value={second[p.id]} onChange={(v) => set(setSecond)(p.id, v)} allowNegative={false} />
            </PlayerRow>
          ))}
          <Label>Stöck (20 Punkte)</Label>
          <View style={ui.wrap}>
            <Chip title="Keine" active={stoeck === null} onPress={() => setStoeck(null)} />
            {ps.map((p) => (
              <Chip key={p.id} title={p.name} active={stoeck === p.id} onPress={() => setStoeck(p.id)} />
            ))}
          </View>
          {data && data.kind === 'jassSchieber' ? (
            <Hint>{`Diese Runde: ${ps.map((p) => `${p.name} ${roundScores(data!, ps)[p.id]}`).join(' · ')}`}</Hint>
          ) : null}
        </>
      ) : null}

      {kind === 'differenzler'
        ? ps.map((p) => (
            <Card key={p.id} style={{ flexDirection: 'column', alignItems: 'stretch', gap: 10 }}>
              <View style={ui.row}>
                <Avatar name={p.name} color={p.color} size={32} />
                <Text style={[ui.cardTitle, { flex: 1 }]}>{p.name}</Text>
                <Text style={ui.meta}>{`Total ${totals(game)[p.id]}`}</Text>
              </View>
              <View style={[ui.row, { justifyContent: 'space-between' }]}>
                <Text style={ui.meta}>Ansage</Text>
                <NumberField value={scores[p.id]} onChange={(v) => set(setScores)(p.id, v)} allowNegative={false} />
                <Text style={ui.meta}>Erzielt</Text>
                <NumberField value={second[p.id]} onChange={(v) => set(setSecond)(p.id, v)} allowNegative={false} />
              </View>
            </Card>
          ))
        : null}

      {kind === 'coiffeur' ? (
        <>
          <Label>Welches Team spielt?</Label>
          <View style={ui.wrap}>
            {ps.map((p) => (
              <Chip
                key={p.id}
                title={p.name}
                active={teamId === p.id}
                onPress={() => {
                  setTeamId(p.id);
                  setModeName(null);
                }}
              />
            ))}
          </View>
          <Label>Spielart</Label>
          <View style={ui.wrap}>
            {openCoiffeurModes(game, teamId).map((n) => {
              const m = modes.find((x) => x.name === n)!;
              return <Chip key={n} title={`${n} ×${m.factor}`} active={modeName === n} onPress={() => setModeName(n)} />;
            })}
          </View>
          {openCoiffeurModes(game, teamId).length === 0 ? <Text style={ui.meta}>Dieses Team hat alle Spielarten gespielt.</Text> : null}
          <Label>Punkte</Label>
          <Card style={{ justifyContent: 'space-between' }}>
            <NumberField value={scores[teamId]} onChange={(v) => set(setScores)(teamId, v)} width={120} allowNegative={false} />
            <Pressable onPress={() => set(setScores)(teamId, JASS_MATCH)}>
              <Text style={s.link}>Match 257</Text>
            </Pressable>
            {mode && scores[teamId] !== null ? <Text style={s.value}>{`= ${scores[teamId]! * mode.factor}`}</Text> : null}
          </Card>
        </>
      ) : null}

      {kind === 'placement' ? (
        <>
          <Label>Wer ist fertig? Der Reihe nach antippen</Label>
          <View style={ui.wrap}>
            {ps
              .filter((p) => !order.includes(p.id) && order.length < ps.length - 1)
              .map((p) => (
                <Chip key={p.id} title={p.name} onPress={() => setOrder([...order, p.id])} />
              ))}
          </View>
          {(() => {
            const pts = placementPoints(order, ps.map((p) => p.id));
            const full = [...order, ...ps.filter((p) => !order.includes(p.id)).map((p) => p.id)];
            const shown = order.length >= ps.length - 1 ? full : order;
            return shown.map((pid, i) => {
              const p = ps.find((x) => x.id === pid)!;
              return (
                <PlayerRow key={pid} p={p} meta={`${i + 1}. Platz · ${officeName(i, ps.length)}`}>
                  <Text style={s.value}>{`+${pts[pid]}`}</Text>
                </PlayerRow>
              );
            });
          })()}
          {order.length > 0 ? (
            <Pressable onPress={() => setOrder([])} style={{ alignSelf: 'center', padding: 8 }}>
              <Text style={s.link}>Reihenfolge neu antippen</Text>
            </Pressable>
          ) : null}
        </>
      ) : null}

      {kind === 'lives' ? (
        <>
          <Label>Wer verliert ein Leben?</Label>
          {alive(game).map((p) => {
            const on = losers.includes(p.id);
            const left = totals(game)[p.id];
            return (
              <Card key={p.id} onPress={() => setLosers(on ? losers.filter((x) => x !== p.id) : [...losers, p.id])} style={on ? s.picked : undefined}>
                <Avatar name={p.name} color={p.color} />
                <View style={{ flex: 1, gap: 3 }}>
                  <Text style={ui.cardTitle}>{p.name}</Text>
                  <Text style={ui.meta}>{on ? `noch ${left - 1} Leben` : `${left} Leben`}</Text>
                </View>
                <Text style={[s.value, { color: on ? Colors.danger : Colors.faint }]}>{on ? '−1' : '○'}</Text>
              </Card>
            );
          })}
          {game.participants.length > alive(game).length ? (
            <Text style={ui.meta}>{`Draussen: ${game.participants
              .filter((p) => !alive(game).includes(p))
              .map((p) => p.name)
              .join(', ')}`}</Text>
          ) : null}
        </>
      ) : null}

      {kind === 'wizard'
        ? ps.map((p) => (
            <Card key={p.id} style={{ flexDirection: 'column', alignItems: 'stretch', gap: 10 }}>
              <View style={ui.row}>
                <Avatar name={p.name} color={p.color} size={32} />
                <Text style={[ui.cardTitle, { flex: 1 }]}>{p.name}</Text>
                <Text style={ui.meta}>
                  {scores[p.id] !== null && second[p.id] !== null
                    ? `diese Runde ${wizardPoints(scores[p.id]!, second[p.id]!)}`
                    : `Total ${totals(game)[p.id]}`}
                </Text>
              </View>
              <View style={[ui.row, { justifyContent: 'space-between' }]}>
                <Text style={ui.meta}>Ansage</Text>
                <NumberField value={scores[p.id]} onChange={(v) => set(setScores)(p.id, v)} />
                <Text style={ui.meta}>Stiche</Text>
                <NumberField value={second[p.id]} onChange={(v) => set(setSecond)(p.id, v)} />
              </View>
            </Card>
          ))
        : null}

      {hint ? <Hint>{hint}</Hint> : null}
    </Screen>
  );

  if (!wide) return form;

  // Tablet quer: Dialog über dem abgedunkelten Spielstand
  return (
    <View style={s.backdrop}>
      <Pressable style={StyleSheet.absoluteFill} onPress={() => router.back()} accessibilityLabel="Schliessen" />
      <View style={s.dialog}>
        <View style={s.dialogHead}>
          <Text style={s.dialogTitle}>{title}</Text>
          <Pressable onPress={() => router.back()} accessibilityRole="button">
            <Text style={s.link}>Abbrechen</Text>
          </Pressable>
        </View>
        {form}
      </View>
    </View>
  );
}

/** Ämter beim Arschlöchle: König, Vizekönig, Bürger, Vize-Arschloch, Arschloch. */
function officeName(index: number, count: number): string {
  if (index === 0) return 'König';
  if (index === count - 1) return 'Arschloch';
  if (index === 1 && count > 3) return 'Vizekönig';
  if (index === count - 2 && count > 3) return 'Vize-Arschloch';
  return 'Bürger';
}

function PlayerRow({ p, meta, style, children }: { p: Participant; meta?: string; style?: ViewStyle; children: ReactNode }) {
  const ui = useUi();
  return (
    <Card style={style}>
      <Avatar name={p.name} color={p.color} />
      <View style={{ flex: 1, gap: 3 }}>
        <Text style={ui.cardTitle}>{p.name}</Text>
        {meta ? <Text style={ui.meta}>{meta}</Text> : null}
      </View>
      {children}
    </Card>
  );
}

function Stepper({ value, onChange }: { value: number | null; onChange: (v: number | null) => void }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  return (
    <View style={ui.row}>
      <Pressable style={[s.step, { backgroundColor: Colors.surfaceMuted }]} onPress={() => onChange((value ?? 0) - 1)} accessibilityLabel="Minus 1">
        <Text style={[s.stepText, { color: Colors.primary }]}>−</Text>
      </Pressable>
      <NumberField value={value} onChange={onChange} width={70} />
      <Pressable style={[s.step, { backgroundColor: Colors.primary }]} onPress={() => onChange((value ?? 0) + 1)} accessibilityLabel="Plus 1">
        <Text style={[s.stepText, { color: '#fff' }]}>+</Text>
      </Pressable>
    </View>
  );
}

const sBase = StyleSheet.create({
  team: { flex: 1, flexDirection: 'column', gap: 8 },
  teamName: { fontSize: 15, fontWeight: '600', color: Colors.ink, textAlign: 'center' },
  auto: { width: 120, alignItems: 'center', justifyContent: 'center' },
  autoValue: { fontSize: 22, fontWeight: '700', color: Colors.ink },
  autoNote: { fontSize: 12, fontWeight: '600', color: Colors.primary, textAlign: 'center' },
  link: { color: Colors.primary, fontWeight: '700' },
  value: { fontSize: 18, fontWeight: '700', color: Colors.primary },
  picked: { borderWidth: 2, borderColor: Colors.danger },
  step: { width: 36, height: 36, borderRadius: 18, alignItems: 'center', justifyContent: 'center' },
  stepText: { fontSize: 20, fontWeight: '700' },
  playerList: { gap: 12 },
  playerGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  playerCell: { flexGrow: 1, flexBasis: '45%' },
  backdrop: { flex: 1, backgroundColor: 'rgba(29,36,32,0.45)', alignItems: 'center', justifyContent: 'center' },
  // Breit genug für zwei Spalten mit ±-Taste und Zahlenfeld neben dem Namen
  dialog: { width: 760, maxWidth: '92%', height: '88%', backgroundColor: Colors.background, borderRadius: 24, overflow: 'hidden' },
  dialogHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 20, paddingTop: 18 },
  dialogTitle: { fontSize: 24, fontWeight: '800', color: Colors.ink, letterSpacing: -0.4 },
});
