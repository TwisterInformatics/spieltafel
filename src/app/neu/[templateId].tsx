import { router, Stack, useLocalSearchParams } from 'expo-router';
import { useSQLiteContext } from 'expo-sqlite';
import { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

import { Avatar, Button, Card, Chip, Label, NumberField, Screen, Title, useUi } from '@/components/ui';
import { useScaledStyles } from '@/constants/layout';
import { Colors, PlayerColors } from '@/constants/theme';
import { createGame, recentPlayers } from '@/data/db';
import { getTemplate } from '@/domain/templates';
import type { EndRule, GameSettings, Participant, WinnerRule } from '@/domain/types';

export default function NewGameScreen() {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const { templateId } = useLocalSearchParams<{ templateId: string }>();
  const template = getTemplate(templateId);
  const db = useSQLiteContext();

  const isTeams = !!template?.teams;
  const initialCount = isTeams ? (template!.teams ?? 2) * (template!.teamSize ?? 2) : Math.max(template?.minPlayers ?? 2, 2);
  const [names, setNames] = useState<string[]>(() => Array.from({ length: initialCount }, () => ''));
  const [recent, setRecent] = useState<string[]>([]);
  const [target, setTarget] = useState<number | null>(template?.end.type === 'target' ? template.end.target : null);
  const [rounds, setRounds] = useState<number | null>(template?.end.type === 'rounds' ? template.end.rounds : null);
  const [winner, setWinner] = useState<WinnerRule>(template?.winner ?? 'highest');
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    recentPlayers(db).then(setRecent);
  }, [db]);

  if (!template) return <Text style={{ padding: 20 }}>Spiel nicht gefunden.</Text>;

  const max = isTeams ? initialCount : template.maxPlayers;
  const filled = names.map((n) => n.trim()).filter(Boolean);
  const enough = isTeams ? filled.length === initialCount : filled.length >= template.minPlayers;
  const settingsOk =
    (template.end.type !== 'target' || (target ?? 0) > 0) && (template.end.type !== 'rounds' || (rounds ?? 0) > 0);

  const setName = (i: number, v: string) => setNames((prev) => prev.map((n, j) => (j === i ? v : n)));
  const fillNext = (name: string) => {
    const i = names.findIndex((n) => !n.trim());
    if (i >= 0) setName(i, name);
    else if (max === null || names.length < max) setNames([...names, name]);
  };

  async function start() {
    if (!template) return;
    setSaving(true);
    let participants: Participant[];
    if (isTeams) {
      const size = template.teamSize ?? 2;
      participants = Array.from({ length: template.teams ?? 2 }, (_, t) => {
        const members = names.slice(t * size, t * size + size).map((n) => n.trim());
        return { id: `t${t + 1}`, name: members.join(' und '), color: PlayerColors[t], members };
      });
    } else {
      participants = filled.map((name, i) => ({ id: `p${i + 1}`, name, color: PlayerColors[i % PlayerColors.length] }));
    }
    let end: EndRule = template.end;
    if (end.type === 'target') end = { type: 'target', target: target ?? end.target };
    if (end.type === 'rounds') end = { type: 'rounds', rounds: rounds ?? end.rounds };
    const settings: GameSettings = { winner, end, modes: template.modes };
    const id = await createGame(db, template.id, participants, settings);
    router.replace(`/partie/${id}`);
  }

  const used = new Set(filled.map((n) => n.toLowerCase()));
  const suggestions = recent.filter((r) => !used.has(r.toLowerCase())).slice(0, 8);

  return (
    <Screen footer={<Button title="Partie starten" onPress={start} disabled={!enough || !settingsOk || saving} />}>
      <Stack.Screen options={{ title: template.name }} />
      <Title sub={template.description}>Neue Partie</Title>

      {isTeams ? (
        Array.from({ length: template.teams ?? 2 }, (_, t) => (
          <View key={t} style={{ gap: 10 }}>
            <Label>{`Team ${t + 1}`}</Label>
            {Array.from({ length: template.teamSize ?? 2 }, (_, k) => {
              const i = t * (template.teamSize ?? 2) + k;
              return <NameRow key={i} index={i} color={PlayerColors[t]} value={names[i]} onChange={(v) => setName(i, v)} />;
            })}
          </View>
        ))
      ) : (
        <>
          <Label>Spieler</Label>
          {names.map((n, i) => (
            <NameRow
              key={i}
              index={i}
              color={PlayerColors[i % PlayerColors.length]}
              value={n}
              onChange={(v) => setName(i, v)}
              onRemove={names.length > Math.max(template.minPlayers, 1) ? () => setNames(names.filter((_, j) => j !== i)) : undefined}
            />
          ))}
          {max === null || names.length < max ? (
            <Pressable style={s.add} onPress={() => setNames([...names, ''])} accessibilityRole="button">
              <Text style={s.addText}>+ Spieler hinzufügen</Text>
            </Pressable>
          ) : null}
        </>
      )}

      {suggestions.length > 0 ? (
        <>
          <Label>Zuletzt gespielt</Label>
          <View style={ui.wrap}>
            {suggestions.map((r) => (
              <Chip key={r} title={r} onPress={() => fillNext(r)} />
            ))}
          </View>
        </>
      ) : null}

      <Label>Regeln</Label>
      {template.end.type === 'target' ? (
        <Card style={s.setting}>
          <Text style={s.settingText}>Spielende bei</Text>
          <NumberField value={target} onChange={setTarget} width={100} />
        </Card>
      ) : null}
      {template.end.type === 'rounds' ? (
        <Card style={s.setting}>
          <Text style={s.settingText}>Anzahl Runden</Text>
          <NumberField value={rounds} onChange={setRounds} width={100} />
        </Card>
      ) : null}
      {template.end.type === 'manual' ? (
        <Card style={s.setting}>
          <Text style={s.settingText}>Spielende</Text>
          <Text style={s.value}>Wenn ihr wollt</Text>
        </Card>
      ) : null}
      {template.end.type === 'allModes' ? (
        <Card style={s.setting}>
          <Text style={s.settingText}>Spielarten pro Team</Text>
          <Text style={s.value}>{template.modes?.length ?? 0}</Text>
        </Card>
      ) : null}
      {template.winnerAdjustable ? (
        <Card style={s.setting}>
          <Text style={s.settingText}>Sieger</Text>
          <View style={ui.row}>
            <Chip title="Höchste" active={winner === 'highest'} onPress={() => setWinner('highest')} />
            <Chip title="Tiefste" active={winner === 'lowest'} onPress={() => setWinner('lowest')} />
          </View>
        </Card>
      ) : null}
    </Screen>
  );
}

function NameRow({
  index,
  color,
  value,
  onChange,
  onRemove,
}: {
  index: number;
  color: string;
  value: string;
  onChange: (v: string) => void;
  onRemove?: () => void;
}) {
  const s = useScaledStyles(sBase);
  return (
    <Card style={s.nameRow}>
      <Avatar name={value || String(index + 1)} color={color} />
      <View style={{ flex: 1, minWidth: 0 }}>
        <TextInput
          value={value}
          onChangeText={onChange}
          placeholder={`Spieler ${index + 1}`}
          placeholderTextColor={Colors.faint}
          style={s.nameInput}
          autoCapitalize="words"
          autoCorrect={false}
        />
      </View>
      {onRemove ? (
        <Pressable onPress={onRemove} hitSlop={10} accessibilityLabel="Spieler entfernen">
          <Text style={s.remove}>×</Text>
        </Pressable>
      ) : null}
    </Card>
  );
}

const sBase = StyleSheet.create({
  nameRow: { paddingVertical: 10 },
  nameInput: { width: '100%', fontSize: 17, fontWeight: '600', color: Colors.ink, paddingVertical: 8 },
  remove: { fontSize: 24, color: Colors.faint, paddingHorizontal: 4 },
  add: {
    borderWidth: 2,
    borderStyle: 'dashed',
    borderColor: '#b9c4bd',
    borderRadius: 16,
    padding: 14,
    alignItems: 'center',
  },
  addText: { color: Colors.primary, fontWeight: '600', fontSize: 15 },
  setting: { justifyContent: 'space-between' },
  settingText: { fontSize: 16, color: Colors.ink, flex: 1 },
  value: { fontSize: 16, fontWeight: '700', color: Colors.primary },
});
