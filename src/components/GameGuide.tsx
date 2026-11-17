import * as WebBrowser from 'expo-web-browser';
import { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { useScaledStyles } from '@/constants/layout';
import { Colors } from '@/constants/theme';
import type { GameTemplate } from '@/domain/types';

import { useUi } from './ui';

/** Aufklappbare Kurzanleitung mit Link zur ausführlichen Anleitung. */
export function GameGuide({ template, initiallyOpen = false }: { template: GameTemplate; initiallyOpen?: boolean }) {
  const ui = useUi();
  const s = useScaledStyles(sBase);
  const [open, setOpen] = useState(initiallyOpen);
  const rules = template.rules;

  return (
    <View style={s.box}>
      <Pressable
        onPress={() => setOpen(!open)}
        style={s.head}
        accessibilityRole="button"
        accessibilityState={{ expanded: open }}
        accessibilityLabel={`Kurzanleitung ${template.name}`}>
        <Text style={ui.cardTitle}>Kurzanleitung</Text>
        <Text style={s.toggle}>{open ? '−' : '+'}</Text>
      </Pressable>
      {open ? (
        <View style={s.body}>
          {template.guide.map((line, i) => (
            <View key={i} style={s.item}>
              <Text style={s.bullet}>•</Text>
              <Text style={s.text}>{line}</Text>
            </View>
          ))}
          {rules ? (
            <Pressable onPress={() => WebBrowser.openBrowserAsync(rules.url)} accessibilityRole="link" style={s.linkRow}>
              <Text style={s.link}>{`Ausführliche Anleitung · ${rules.source}`}</Text>
            </Pressable>
          ) : null}
        </View>
      ) : null}
    </View>
  );
}

const sBase = StyleSheet.create({
  box: { backgroundColor: Colors.surface, borderRadius: 16, paddingHorizontal: 16 },
  head: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 14 },
  toggle: { fontSize: 22, fontWeight: '700', color: Colors.primary, width: 24, textAlign: 'center' },
  body: { gap: 8, paddingBottom: 16 },
  item: { flexDirection: 'row', gap: 8 },
  bullet: { fontSize: 15, color: Colors.primary, lineHeight: 21 },
  text: { flex: 1, fontSize: 15, color: Colors.ink, lineHeight: 21 },
  linkRow: { paddingTop: 6 },
  link: { color: Colors.primary, fontWeight: '700', fontSize: 15 },
});
