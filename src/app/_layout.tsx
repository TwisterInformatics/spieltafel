import { Stack } from 'expo-router';
import { SQLiteProvider } from 'expo-sqlite';
import { StatusBar } from 'expo-status-bar';

import { useLayout } from '@/constants/layout';
import { Colors } from '@/constants/theme';
import { DATABASE_NAME, migrate } from '@/data/db';
import { SettingsProvider } from '@/data/settings';

export default function RootLayout() {
  const { k, wide } = useLayout();
  return (
    <SQLiteProvider databaseName={DATABASE_NAME} onInit={migrate}>
      <SettingsProvider>
        <StatusBar style="dark" />
        <Stack
          screenOptions={{
            headerStyle: { backgroundColor: Colors.background },
            headerShadowVisible: false,
            headerTintColor: Colors.primary,
            headerTitleStyle: { color: Colors.ink, fontWeight: '700', fontSize: 17 * k },
            headerBackTitle: 'Zurück',
            contentStyle: { backgroundColor: Colors.background },
          }}>
          <Stack.Screen name="index" options={{ title: 'Spieltafel', headerShown: false }} />
          <Stack.Screen name="verlauf" options={{ title: '' }} />
          <Stack.Screen name="einstellungen" options={{ title: '' }} />
          <Stack.Screen name="neu/[templateId]" options={{ title: 'Neue Partie' }} />
          <Stack.Screen name="partie/[id]/index" options={{ title: 'Spielstand' }} />
          <Stack.Screen
            name="partie/[id]/runde"
            options={
              wide
                ? // Tablet quer: als Dialog über dem Spielstand, Kopfzeile zeichnet der Screen selbst
                  { title: 'Runde erfassen', presentation: 'transparentModal', animation: 'fade', headerShown: false, contentStyle: { backgroundColor: 'transparent' } }
                : { title: 'Runde erfassen', presentation: 'modal' }
            }
          />
        </Stack>
      </SettingsProvider>
    </SQLiteProvider>
  );
}
