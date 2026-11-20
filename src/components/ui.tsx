import { useEffect, useRef, useState, type ComponentRef, type ReactNode, type RefObject } from 'react';
import {
  Dimensions,
  Keyboard,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
  type ViewStyle,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { keyboardOverlap, useLayout, useScaledStyles } from '@/constants/layout';
import { Colors, Radius, Space } from '@/constants/theme';

import { formatNumberInput, parseNumberInput, toggleSign } from './numberInput';

// Android: "numeric" ist die Zahlentastatur mit Minus-Taste.
// iOS: Die Zahlentastatur hat kein Minus, darum die Tastatur mit Zahlen und Satzzeichen.
const NUMBER_KEYBOARD = Platform.OS === 'ios' ? 'numbers-and-punctuation' : 'numeric';

type ViewRef = ComponentRef<typeof View>;

/**
 * iOS verkleinert das Fenster nicht, wenn die Tastatur erscheint (Android macht das selbst).
 * Liefert, wie weit die Tastatur den Container überdeckt, damit Inhalt und Fusszeile darüber bleiben.
 */
function useKeyboardOverlap(container: RefObject<ViewRef | null>): number {
  const [overlap, setOverlap] = useState(0);
  const { isTablet } = useLayout();
  useEffect(() => {
    if (Platform.OS !== 'ios') return;
    const show = Keyboard.addListener('keyboardWillShow', (e) => {
      container.current?.measureInWindow((_x: number, y: number, _w: number, h: number) => {
        setOverlap(keyboardOverlap(y + h, Dimensions.get('window').height, e.endCoordinates.screenY, isTablet));
      });
    });
    const hide = Keyboard.addListener('keyboardWillHide', () => setOverlap(0));
    return () => {
      show.remove();
      hide.remove();
    };
  }, [container, isTablet]);
  return overlap;
}

export function Screen({ children, footer }: { children: ReactNode; footer?: ReactNode }) {
  const styles = useUi();
  const width = useContentWidth();
  const container = useRef<ViewRef>(null);
  const overlap = useKeyboardOverlap(container);
  return (
    <View ref={container} style={[styles.screen, { paddingBottom: overlap }]} collapsable={false}>
      <SafeAreaView style={styles.screen} edges={overlap > 0 ? ['left', 'right'] : ['bottom', 'left', 'right']}>
        <ScrollView contentContainerStyle={[styles.content, width]} keyboardShouldPersistTaps="handled">
          {children}
        </ScrollView>
        {footer ? <View style={[styles.footer, width]}>{footer}</View> : null}
      </SafeAreaView>
    </View>
  );
}

export function Title({ children, sub }: { children: string; sub?: string }) {
  const styles = useUi();
  return (
    <View style={styles.titleBox}>
      <Text style={styles.title}>{children}</Text>
      {sub ? <Text style={styles.sub}>{sub}</Text> : null}
    </View>
  );
}

export function Label({ children }: { children: string }) {
  const styles = useUi();
  return <Text style={styles.label}>{children.toUpperCase()}</Text>;
}

export function Card({ children, style, onPress }: { children: ReactNode; style?: ViewStyle; onPress?: () => void }) {
  const styles = useUi();
  if (onPress) {
    return (
      <Pressable onPress={onPress} style={({ pressed }) => [styles.card, style, pressed && { opacity: 0.7 }]}>
        {children}
      </Pressable>
    );
  }
  return <View style={[styles.card, style]}>{children}</View>;
}

export function Button({
  title,
  onPress,
  variant = 'primary',
  disabled,
}: {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'secondary' | 'danger';
  disabled?: boolean;
}) {
  const styles = useUi();
  const primary = variant === 'primary';
  const color = variant === 'danger' ? Colors.danger : Colors.primary;
  return (
    <Pressable
      accessibilityRole="button"
      disabled={disabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.button,
        primary ? { backgroundColor: color } : { backgroundColor: Colors.surface, borderWidth: 2, borderColor: color },
        (pressed || disabled) && { opacity: disabled ? 0.4 : 0.8 },
      ]}>
      <Text style={[styles.buttonText, { color: primary ? '#fff' : color }]}>{title}</Text>
    </Pressable>
  );
}

export function Chip({ title, active, onPress }: { title: string; active?: boolean; onPress?: () => void }) {
  const styles = useUi();
  return (
    <Pressable onPress={onPress} style={[styles.chip, active && { backgroundColor: Colors.primary }]}>
      <Text style={[styles.chipText, active && { color: '#fff' }]}>{title}</Text>
    </Pressable>
  );
}

export function Avatar({ name, color, size: baseSize = 40 }: { name: string; color: string; size?: number }) {
  const styles = useUi();
  const size = baseSize * useLayout().k;
  return (
    <View style={[styles.avatar, { width: size, height: size, borderRadius: size / 2, backgroundColor: color }]}>
      <Text style={styles.avatarText}>{name.trim().charAt(0).toUpperCase() || '?'}</Text>
    </View>
  );
}

export function GameIcon({ letter, color }: { letter: string; color: string }) {
  const styles = useUi();
  return (
    <View style={[styles.gameIcon, { backgroundColor: color }]}>
      <Text style={styles.gameIconText}>{letter}</Text>
    </View>
  );
}

/** Zahlenfeld, das auch negative Zahlen erlaubt. Leeres Feld = null. */
export function NumberField({
  value,
  onChange,
  width = 84,
  placeholder = '0',
  allowNegative = true,
}: {
  value: number | null;
  onChange: (v: number | null) => void;
  width?: number;
  placeholder?: string;
  /** Zeigt eine ±-Taste. Viele Android-Tastaturen (z.B. Samsung) haben im Zahlenfeld kein Minus. */
  allowNegative?: boolean;
}) {
  const styles = useUi();
  const { k } = useLayout();
  // Eigener Text, damit ein angefangenes "-" stehen bleibt, bis die Ziffern kommen.
  const [text, setText] = useState(() => formatNumberInput(value));
  useEffect(() => {
    // Wert von aussen geändert (z.B. Formular zurückgesetzt): Feld nachziehen.
    setText((current) => (parseNumberInput(current).value === value ? current : formatNumberInput(value)));
  }, [value]);
  const apply = (parsed: { text: string; value: number | null }) => {
    setText(parsed.text);
    onChange(parsed.value);
  };
  const input = (
    <TextInput
      value={text}
      onChangeText={(t) => apply(parseNumberInput(t))}
      keyboardType={NUMBER_KEYBOARD}
      placeholder={placeholder}
      placeholderTextColor="#a3a8a4"
      selectTextOnFocus
      style={[styles.number, { width: width * k }]}
    />
  );
  if (!allowNegative) return input;
  const negative = text.startsWith('-');
  return (
    <View style={styles.numberRow}>
      <Pressable
        onPress={() => apply(toggleSign(text))}
        hitSlop={6}
        accessibilityRole="button"
        accessibilityLabel={negative ? 'Positiv machen' : 'Negativ machen'}
        style={({ pressed }) => [styles.signButton, negative && styles.signButtonActive, pressed && { opacity: 0.7 }]}>
        <Text style={[styles.signText, negative && styles.signTextActive]}>±</Text>
      </Pressable>
      {input}
    </View>
  );
}

export function Hint({ children }: { children: string }) {
  const styles = useUi();
  return (
    <View style={styles.hint}>
      <Text style={styles.hintText}>{children}</Text>
    </View>
  );
}

const baseStyles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Space.screen, gap: Space.gap, paddingBottom: 32 },
  footer: { paddingHorizontal: Space.screen, paddingTop: 8, paddingBottom: 12, gap: 10 },
  titleBox: { gap: 2, marginBottom: 4 },
  title: { fontSize: 28, fontWeight: '800', color: Colors.ink, letterSpacing: -0.4 },
  sub: { fontSize: 14, color: Colors.muted },
  label: { fontSize: 13, fontWeight: '700', color: Colors.muted, letterSpacing: 0.6, marginTop: 6 },
  card: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.card,
    paddingVertical: 14,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 3,
    shadowOffset: { width: 0, height: 1 },
    elevation: 1,
  },
  button: { height: 54, borderRadius: Radius.card, alignItems: 'center', justifyContent: 'center' },
  buttonText: { fontSize: 17, fontWeight: '700' },
  chip: { paddingVertical: 8, paddingHorizontal: 14, borderRadius: Radius.pill, backgroundColor: Colors.surface },
  chipText: { fontSize: 14, fontWeight: '500', color: '#3b443f' },
  avatar: { alignItems: 'center', justifyContent: 'center' },
  avatarText: { color: '#fff', fontWeight: '700', fontSize: 16 },
  gameIcon: { width: 46, height: 46, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
  gameIconText: { color: '#fff', fontWeight: '800', fontSize: 18 },
  number: {
    height: 46,
    borderRadius: Radius.input,
    backgroundColor: Colors.surfaceMuted,
    textAlign: 'center',
    fontSize: 22,
    fontWeight: '700',
    color: Colors.ink,
  },
  numberRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  signButton: {
    width: 38,
    height: 46,
    borderRadius: Radius.input,
    backgroundColor: Colors.surfaceMuted,
    alignItems: 'center',
    justifyContent: 'center',
  },
  signButtonActive: { backgroundColor: Colors.primary },
  signText: { fontSize: 22, fontWeight: '700', color: Colors.primary },
  signTextActive: { color: '#fff' },
  hint: { backgroundColor: Colors.hintBg, borderRadius: Radius.input, padding: 12 },
  hintText: { fontSize: 13, color: Colors.hintInk, lineHeight: 18 },
  row: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  wrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  cardTitle: { fontSize: 17, fontWeight: '600', color: Colors.ink },
  meta: { fontSize: 13, color: Colors.muted },
});

/** Gemeinsame Stile, auf Tablets automatisch vergrössert. */
export function useUi() {
  return useScaledStyles(baseStyles);
}

/** Zentriert den Inhalt und begrenzt seine Breite auf Tablets. */
export function useContentWidth() {
  const { maxWidth } = useLayout();
  return maxWidth ? { width: '100%' as const, maxWidth, alignSelf: 'center' as const } : null;
}
