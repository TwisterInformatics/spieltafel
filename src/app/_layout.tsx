import { Stack } from 'expo-router';
import { SQLiteProvider } from 'expo-sqlite';
import { StatusBar } from 'expo-status-bar';

import { Colors } from '@/constants/theme';
import { DATABASE_NAME, migrate } from '@/data/db';

export default function RootLayout() {
  return (
    <SQLiteProvider databaseName={DATABASE_NAME} onInit={migrate}>
      <StatusBar style="dark" />
      <Stack
        screenOptions={{
          headerStyle: { backgroundColor: Colors.background },
          headerShadowVisible: false,
          headerTintColor: Colors.primary,
          headerTitleStyle: { color: Colors.ink, fontWeight: '700' },
          headerBackTitle: 'Zurück',
          contentStyle: { backgroundColor: Colors.background },
        }}>
        <Stack.Screen name="index" options={{ title: 'Spieltafel', headerShown: false }} />
        <Stack.Screen name="verlauf" options={{ title: '' }} />
        <Stack.Screen name="neu/[templateId]" options={{ title: 'Neue Partie' }} />
        <Stack.Screen name="partie/[id]/index" options={{ title: 'Spielstand' }} />
        <Stack.Screen name="partie/[id]/runde" options={{ title: 'Runde erfassen', presentation: 'modal' }} />
      </Stack>
    </SQLiteProvider>
  );
}
