import type { GameTemplate, JassMode } from './types';

/** Trumpf-Faktoren beim Schieber. Viele Runden spielen eigene Hausregeln, deshalb nur Vorgabe. */
export const SCHIEBER_MODES: JassMode[] = [
  { name: 'Eicheln', factor: 1 },
  { name: 'Schellen', factor: 1 },
  { name: 'Rosen', factor: 2 },
  { name: 'Schilten', factor: 2 },
  { name: 'Obenabe', factor: 3 },
  { name: 'Undenufe', factor: 3 },
];

/** Spielarten beim Coiffeur. Jede Spielart spielt jedes Team einmal. Faktoren als Vorgabe. */
export const COIFFEUR_MODES: JassMode[] = [
  { name: 'Eicheln', factor: 1 },
  { name: 'Schellen', factor: 2 },
  { name: 'Rosen', factor: 3 },
  { name: 'Schilten', factor: 4 },
  { name: 'Obenabe', factor: 5 },
  { name: 'Undenufe', factor: 6 },
];

export const TEMPLATES: GameTemplate[] = [
  {
    id: 'skyjo',
    name: 'Skyjo',
    category: 'Kartenspiel',
    description: '2 bis 8 Spieler · tiefste Summe gewinnt',
    keywords: ['skyjo', 'karten', 'magilano'],
    kind: 'skyjo',
    minPlayers: 2,
    maxPlayers: 8,
    winner: 'lowest',
    end: { type: 'target', target: 100 },
    guide: {
      goal: 'Am Ende möglichst wenige Punkte vor sich liegen haben.',
      steps: [
        'Jeder legt 12 Karten verdeckt in 3 Reihen zu 4 aus und deckt zwei davon auf.',
        'Wer dran ist, nimmt eine Karte vom Stapel oder vom Ablagestapel und tauscht sie gegen eine eigene Karte. Oder man wirft sie ab und deckt eine eigene Karte auf.',
        'Drei gleiche Zahlen in einer Spalte werden abgeräumt.',
        'Hat jemand alle Karten aufgedeckt, sind alle anderen noch einmal dran.',
      ],
      scoring: [
        'Alle decken auf und zählen ihre Karten zusammen.',
        'Wer die Runde beendet hat und nicht allein am wenigsten Punkte hat, bekommt seine Punkte doppelt. Spieltafel rechnet das selbst.',
        'Sobald jemand 100 Punkte hat, ist Schluss. Wer am wenigsten Punkte hat, gewinnt.',
      ],
      link: { title: 'Wikipedia: Skyjo', url: 'https://de.wikipedia.org/wiki/Skyjo' },
    },
  },
  {
    id: 'jass-schieber',
    name: 'Jass Schieber',
    category: 'Kartenspiel',
    description: '2 Teams · Zielpunkte erreichen',
    keywords: ['jass', 'schieber', 'jassen', 'karten', 'schweiz'],
    kind: 'jassSchieber',
    minPlayers: 2,
    maxPlayers: 2,
    teams: 2,
    teamSize: 2,
    winner: 'highest',
    end: { type: 'target', target: 2500 },
    modes: SCHIEBER_MODES,
    guide: {
      goal: 'Zwei Teams spielen gegeneinander. Wer zuerst die Zielpunkte hat, gewinnt.',
      steps: [
        'Vier Spieler, die Partner sitzen sich gegenüber. Jeder bekommt 9 Karten.',
        'Wer ansagt, wählt einen Trumpf, Obenabe oder Undenufe. Oder er schiebt, dann wählt sein Partner.',
        'Farbe muss angegeben werden, trumpfen ist immer erlaubt. Den Trumpf-Under (Puur) muss man nie angeben.',
        'Weis (Folgen, vier Gleiche) und Stöck (König und Ober im Trumpf) geben Zusatzpunkte.',
      ],
      scoring: [
        'Eine Runde hat 157 Punkte. Ihr tragt die Punkte eines Teams ein, die Gegenpunkte rechnet die App.',
        'Macht ein Team alle Stiche, ist es ein Match mit 257 Punkten.',
        'Die Punkte zählen je nach Trumpf einfach, doppelt oder dreifach.',
        'Wer zuerst die Zielpunkte erreicht (Vorgabe 2500), gewinnt.',
      ],
      link: { title: 'Jass-Wiki: Schieber', url: 'https://jasswiki.ch/schieber/' },
    },
  },
  {
    id: 'jass-differenzler',
    name: 'Jass Differenzler',
    category: 'Kartenspiel',
    description: '4 Spieler · Ansage treffen, tiefste Differenz gewinnt',
    keywords: ['jass', 'differenzler', 'jassen', 'karten', 'schweiz'],
    kind: 'differenzler',
    minPlayers: 4,
    maxPlayers: 4,
    winner: 'lowest',
    end: { type: 'rounds', rounds: 12 },
    guide: {
      goal: 'Vor jeder Runde ansagen, wie viele Punkte man macht, und möglichst genau treffen.',
      steps: [
        'Jeder spielt für sich. Zu viert bekommt jeder 9 Karten.',
        'Die unterste Karte zeigt die Trumpffarbe.',
        'Jeder sagt an, wie viele Punkte er machen wird.',
        'Gespielt wird wie beim Jass. Alle Stiche zusammen ergeben 157 Punkte.',
      ],
      scoring: [
        'Strafpunkte sind der Abstand zwischen Ansage und erzielten Punkten.',
        'Nach 12 Runden gewinnt, wer am wenigsten Strafpunkte hat.',
      ],
      link: {
        title: 'Jasswelt: Differenzler',
        url: 'https://www.jasswelt.ch/blogs/neuigkeiten/differenzler-jassregel',
      },
    },
  },
  {
    id: 'jass-coiffeur',
    name: 'Jass Coiffeur',
    category: 'Kartenspiel',
    description: '2 Teams · jede Spielart einmal',
    keywords: ['jass', 'coiffeur', 'jassen', 'karten', 'schweiz'],
    kind: 'coiffeur',
    minPlayers: 2,
    maxPlayers: 2,
    teams: 2,
    teamSize: 2,
    winner: 'highest',
    end: { type: 'allModes' },
    modes: COIFFEUR_MODES,
    guide: {
      goal: 'Jedes Team spielt jede Spielart genau einmal. Jede Spielart hat ihren eigenen Faktor.',
      steps: [
        'Zwei Teams zu zwei Spielern, jeder bekommt 9 Karten.',
        'Wer ansagt, wählt eine Spielart, die sein Team noch nicht gespielt hat, oder schiebt.',
        'Gespielt wird wie beim Schieber.',
      ],
      scoring: [
        'Die Punkte des spielenden Teams werden mit dem Faktor der Spielart multipliziert.',
        'Spieltafel gibt Eicheln ×1 bis Undenufe ×6 vor und zeigt, welche Spielarten noch offen sind.',
        'Sind alle Spielarten gespielt, gewinnt das Team mit mehr Punkten.',
      ],
      link: { title: 'Jass-Wiki: Coiffeur', url: 'https://jasswiki.ch/varianten/coiffeur/' },
    },
  },
  {
    id: 'tschau-sepp',
    name: 'Tschau Sepp',
    category: 'Kartenspiel',
    description: '2 bis 8 Spieler · wer 200 erreicht, verliert',
    keywords: ['tschau', 'sepp', 'mau mau', 'jasskarten', 'karten', 'schweiz', 'kinder'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 8,
    winner: 'lowest',
    end: { type: 'target', target: 200 },
    guide: {
      goal: 'Als Erster alle Karten loswerden.',
      steps: [
        'Gespielt wird mit Jasskarten, ab 5 Spielern mit zwei Spielen. Jeder bekommt gleich viele Karten, der Rest ist der Stapel.',
        'Man legt eine Karte gleicher Farbe oder gleicher Höhe auf die offene Karte. Wer nicht kann, zieht eine Karte.',
        'Siebner, Achter, Ass und Under haben Sonderregeln, die je nach Region anders sind. Macht sie vor dem Spiel ab.',
        'Bei der zweitletzten Karte sagt man «Tschau», bei der letzten «Sepp». Wer es vergisst, nimmt 4 Karten auf.',
      ],
      scoring: [
        'Ist jemand fertig, zählen die anderen die Punkte ihrer Handkarten. Welche Karte wie viel zählt, macht ihr vorher ab.',
        'Endet jemand mit einem Under, zählen alle Punkte doppelt.',
        'Wer 200 Punkte erreicht, hat verloren. Es gewinnt, wer am wenigsten hat.',
      ],
      link: { title: 'Jasswelt: Tschau Sepp', url: 'https://www.jasswelt.ch/blogs/neuigkeiten/tschau-sepp-jassregel' },
    },
  },
  {
    id: 'arschloechle',
    name: 'Arschlöchle',
    category: 'Kartenspiel',
    description: '3 bis 10 Spieler · Punkte nach Platz',
    keywords: ['arschlöchle', 'arschloch', 'präsident', 'president', 'könig', 'karten'],
    kind: 'placement',
    minPlayers: 3,
    maxPlayers: 10,
    winner: 'highest',
    end: { type: 'manual' },
    guide: {
      goal: 'Möglichst früh alle Karten loswerden und König werden.',
      steps: [
        'Alle Karten werden verteilt.',
        'Wer anfängt, legt eine Karte oder mehrere gleiche aus. Die Nächsten legen gleich viele höhere Karten oder passen.',
        'Passen alle, räumt wer zuletzt gelegt hat ab und spielt neu aus.',
        'Vor der nächsten Runde gibt das Arschloch dem König seine besten Karten und bekommt dafür die schlechtesten.',
      ],
      scoring: [
        'Im Original gibt es keine Punkte, nur Ämter. Spieltafel zählt trotzdem mit, damit es einen Gesamtsieger gibt.',
        'Tippt beim Erfassen die Spieler in der Reihenfolge an, in der sie fertig wurden.',
        'Der König bekommt so viele Punkte, wie er Mitspieler hat, jeder Platz danach einen weniger, das Arschloch 0.',
        'Ihr hört auf, wann ihr wollt. Am meisten Punkte gewinnt.',
      ],
      link: { title: 'Wikipedia: Arschloch', url: 'https://de.wikipedia.org/wiki/Arschloch_(Kartenspiel)' },
    },
  },
  {
    id: 'gaemsch',
    name: 'Gämsch',
    category: 'Kartenspiel',
    description: '2 Teams · geheime Zeichen, auf 10 Punkte',
    keywords: ['gämsch', 'gemsch', 'gämschen', 'jasskarten', 'karten', 'schweiz', 'zeichen'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 2,
    teams: 2,
    teamSize: 2,
    winner: 'highest',
    end: { type: 'target', target: 10 },
    guide: {
      goal: 'Vier gleiche Karten sammeln und es dem Partner heimlich zeigen.',
      steps: [
        'Gespielt wird in Zweierteams, die Partner sitzen sich gegenüber. Jedes Team macht vorher ein geheimes Zeichen ab.',
        'Jeder bekommt 4 Karten, 4 Karten liegen offen in der Mitte. Alle tauschen gleichzeitig mit der Mitte, bis niemand mehr will. Dann kommen 4 neue Karten in die Mitte.',
        'Hat jemand vier Gleiche, zum Beispiel alle vier Under, gibt er das Zeichen. Der Partner ruft «Gämsch».',
        'Glaubt man, die Gegner haben ein Gämsch, ruft man «Gegengämsch».',
      ],
      scoring: [
        'Gämsch gibt 1 Punkt, Doppelgämsch 2 Punkte.',
        'Ein falsches Gämsch oder ein richtiges Gegengämsch gibt den Gegnern 1 Punkt.',
        'Wer das Zeichen der Gegner errät, bekommt 2 Punkte.',
        'Gespielt wird auf 10 Punkte.',
      ],
      link: { title: 'Jassverzeichnis: Gämschen', url: 'https://jassverzeichnis.ch/anleitung-gaemschen-oder-gemschen' },
    },
  },
  {
    id: 'schnauz',
    name: 'Schnauz',
    category: 'Kartenspiel',
    description: '2 bis 9 Spieler · Leben verlieren, Letzter gewinnt',
    keywords: ['schnauz', 'schwimmen', '31', 'einunddreissig', 'leben', 'karten'],
    kind: 'lives',
    minPlayers: 2,
    maxPlayers: 9,
    winner: 'highest',
    end: { type: 'lastStanding', lives: 4 },
    guide: {
      goal: 'Mit drei Karten einer Farbe möglichst nahe an 31 Punkte kommen.',
      steps: [
        'Jeder bekommt 3 Karten, 3 Karten liegen offen in der Mitte.',
        'Wer dran ist, tauscht eine oder alle drei Karten mit der Mitte oder schiebt.',
        'Wer zufrieden ist, klopft. Danach sind alle anderen noch einmal dran.',
        'Gezählt wird nur eine Farbe: Ass 11, Bilder und Zehner 10, Zahlen nach Augen. Drei gleiche Karten zählen 30½.',
      ],
      scoring: [
        'Wer am wenigsten hat, verliert ein Leben. Tippt ihn beim Erfassen an.',
        'Jeder hat 3 Leben und darf danach noch einmal «schwimmen». Darum startet die App mit 4 Leben.',
        'Wer keine Leben mehr hat, ist draussen. Der Letzte im Spiel gewinnt.',
      ],
      link: { title: 'Wikipedia: Schwimmen', url: 'https://de.wikipedia.org/wiki/Schwimmen_(Kartenspiel)' },
    },
  },
  {
    id: 'uno',
    name: 'Uno',
    category: 'Kartenspiel',
    description: '2 bis 10 Spieler · wer 500 erreicht, verliert',
    keywords: ['uno', 'karten', 'familie', 'kinder'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 10,
    winner: 'lowest',
    end: { type: 'target', target: 500 },
    guide: {
      goal: 'Als Erster alle Karten ablegen.',
      steps: [
        'Jeder bekommt 7 Karten.',
        'Man legt eine Karte gleicher Farbe, gleicher Zahl oder mit gleichem Symbol. Wer nicht kann, zieht eine Karte.',
        'Aussetzen, Richtungswechsel und Zieh 2 wirken auf den Nächsten. Farbwahl und Zieh 4 darf man immer legen.',
        'Bei der zweitletzten Karte ruft man «Uno».',
      ],
      scoring: [
        'Ist jemand fertig, zählen die anderen ihre Handkarten: Zahlen nach Wert, Aktionskarten 20, Farbwahlkarten 50.',
        'Wer 500 Punkte erreicht, hat verloren. Es gewinnt, wer am wenigsten hat.',
        'Nach der offiziellen Regel bekommt der Rundensieger die Punkte der anderen. Dafür nehmt ihr die freie Zählung mit «höchste Summe gewinnt».',
      ],
      link: { title: 'Wikipedia: Uno', url: 'https://de.wikipedia.org/wiki/Uno_(Kartenspiel)' },
    },
  },
  {
    id: 'phase-10',
    name: 'Phase 10',
    category: 'Kartenspiel',
    description: '2 bis 6 Spieler · Strafpunkte, wenigste gewinnen',
    keywords: ['phase', 'phase 10', 'phasen', 'karten', 'familie'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 6,
    winner: 'lowest',
    end: { type: 'manual' },
    guide: {
      goal: 'Als Erster alle zehn Phasen schaffen.',
      steps: [
        'Jeder bekommt 10 Karten.',
        'Wer dran ist, zieht eine Karte und legt eine ab.',
        'Hat man die Karten für seine Phase, zum Beispiel zwei Drillinge, legt man sie aus. Danach darf man bei allen ausgelegten Phasen anlegen.',
        'Wer alle Karten los ist, beendet die Runde. Wer seine Phase geschafft hat, spielt in der nächsten Runde die nächste.',
      ],
      scoring: [
        'Alle anderen zählen ihre Handkarten: 1 bis 9 je 5 Punkte, 10 bis 12 je 10, Aussetzen 15, Joker 25.',
        'Wer zuerst Phase 10 schafft, gewinnt. Schaffen es mehrere in derselben Runde, gewinnt, wer weniger Punkte hat.',
        'Die Phasen merkt ihr euch selbst. Beendet die Partie dann von Hand.',
      ],
      link: { title: 'Wikipedia: Phase 10', url: 'https://de.wikipedia.org/wiki/Phase_10' },
    },
  },
  {
    id: 'romme',
    name: 'Rommé',
    category: 'Kartenspiel',
    description: '2 bis 6 Spieler · Strafpunkte, wenigste gewinnen',
    keywords: ['rommé', 'romme', 'rummy', 'karten', 'joker'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 6,
    winner: 'lowest',
    end: { type: 'manual' },
    guide: {
      goal: 'Alle Karten in Sätzen und Reihen auslegen.',
      steps: [
        'Gespielt wird mit zwei Kartenspielen und Jokern. Jeder bekommt 13 Karten.',
        'Wer dran ist, zieht eine Karte und legt am Schluss eine ab.',
        'Ausgelegt werden Sätze (3 oder 4 gleiche Werte) und Reihen (mindestens 3 Karten einer Farbe in Folge).',
        'Wer alle Karten los ist, beendet die Runde.',
      ],
      scoring: [
        'Die anderen zählen ihre Handkarten: Zahlen nach Augen, Bilder 10, Ass 11, Joker 20.',
        'Gespielt wird, so lange ihr wollt. Wer am wenigsten Punkte hat, gewinnt.',
      ],
      link: { title: 'Wikipedia: Rommé', url: 'https://de.wikipedia.org/wiki/Romm%C3%A9' },
    },
  },
  {
    id: 'wizard',
    name: 'Wizard',
    category: 'Kartenspiel',
    description: '3 bis 6 Spieler · Stiche richtig vorhersagen',
    keywords: ['wizard', 'stiche', 'ansage', 'zauberer', 'karten'],
    kind: 'wizard',
    minPlayers: 3,
    maxPlayers: 6,
    winner: 'highest',
    end: { type: 'rounds', rounds: 15 },
    roundsFromDeck: 60,
    guide: {
      goal: 'Vor jeder Runde genau vorhersagen, wie viele Stiche man macht.',
      steps: [
        'In Runde 1 bekommt jeder 1 Karte, in Runde 2 zwei Karten und so weiter.',
        'Die nächste Karte vom Stapel bestimmt die Trumpffarbe.',
        'Jeder sagt an, wie viele Stiche er machen wird.',
        'Farbe muss angegeben werden. Der erste Zauberer gewinnt den Stich immer, ein Narr nie.',
      ],
      scoring: [
        'Ansage getroffen: 20 Punkte plus 10 je Stich.',
        'Daneben: 10 Punkte Abzug für jeden Stich Unterschied.',
        'Es gibt so viele Runden, wie die 60 Karten reichen, bei 4 Spielern also 15. Am meisten Punkte gewinnt.',
      ],
      link: { title: 'Wikipedia: Wizard', url: 'https://de.wikipedia.org/wiki/Wizard_(Spiel)' },
    },
  },
  {
    id: 'qwirkle',
    name: 'Qwirkle',
    category: 'Legespiel',
    description: '2 bis 8 Spieler · höchste Summe gewinnt',
    keywords: ['qwirkle', 'lege', 'steine'],
    kind: 'rounds',
    minPlayers: 2,
    maxPlayers: 8,
    winner: 'highest',
    end: { type: 'manual' },
    guide: {
      goal: 'Steine nach Farbe oder Form in Reihen legen und dabei Punkte sammeln.',
      steps: [
        'Jeder hat 6 Steine.',
        'Wer dran ist, legt einen oder mehrere Steine in eine Reihe, in der alle Steine die gleiche Farbe oder die gleiche Form haben. Oder er tauscht Steine.',
        'Danach füllt man wieder auf 6 Steine auf.',
      ],
      scoring: [
        'Jede Reihe, die man verlängert, zählt so viele Punkte, wie sie Steine hat.',
        'Eine volle Reihe mit 6 Steinen ist ein Qwirkle und zählt 12 Punkte.',
        'Wer zuerst keine Steine mehr hat, bekommt 6 Punkte dazu. Dann ist Schluss, die höchste Summe gewinnt.',
      ],
      link: { title: 'Wikipedia: Qwirkle', url: 'https://de.wikipedia.org/wiki/Qwirkle' },
    },
  },
  {
    id: 'maexle',
    name: 'Mäxle',
    category: 'Würfelspiel',
    description: '2 oder mehr Spieler · bluffen, Leben verlieren',
    keywords: ['mäxle', 'mäxchen', 'meiern', 'würfel', 'bluff', 'leben'],
    kind: 'lives',
    minPlayers: 2,
    maxPlayers: null,
    winner: 'highest',
    end: { type: 'lastStanding', lives: 6 },
    guide: {
      goal: 'Einen höheren Wurf ansagen als der Vorgänger, notfalls mit Bluff.',
      steps: [
        'Man würfelt verdeckt mit zwei Würfeln im Becher.',
        'Die höhere Zahl ist der Zehner: 3 und 5 ist 53. Pasche (zwei Gleiche) sind höher, am höchsten ist 21, das Mäxle.',
        'Man sagt einen Wurf an, der höher sein muss als der letzte, und gibt den Becher weiter. Die Ansage darf gelogen sein.',
        'Der Nächste glaubt und würfelt selbst, oder er deckt auf.',
      ],
      scoring: [
        'Beim Aufdecken verliert, wer falsch lag: der Lügner oder der Zweifler. Tippt ihn beim Erfassen an.',
        'Jeder startet mit 6 Leben, das könnt ihr beim Start ändern.',
        'Wer keine Leben mehr hat, ist draussen. Der Letzte im Spiel gewinnt.',
      ],
      link: { title: 'Wikipedia: Mäxchen', url: 'https://de.wikipedia.org/wiki/M%C3%A4xchen' },
    },
  },
  {
    id: 'frei',
    name: 'Freie Zählung',
    category: 'Frei',
    description: 'Ohne Spielregeln · beliebig viele Spieler',
    keywords: ['frei', 'punkte', 'zählen', 'block', 'beliebig'],
    kind: 'rounds',
    minPlayers: 1,
    maxPlayers: null,
    winner: 'highest',
    winnerAdjustable: true,
    end: { type: 'manual' },
    guide: {
      goal: 'Punkte für jedes Spiel aufschreiben, das noch nicht in der Liste steht.',
      steps: ['Beliebig viele Spieler erfassen.', 'Nach jeder Runde die Punkte eintragen, auch negative.'],
      scoring: [
        'Beim Start wählt ihr, ob die höchste oder die tiefste Summe gewinnt.',
        'Die Partie endet, wenn ihr sie beendet.',
      ],
    },
  },
];

export function getTemplate(id: string): GameTemplate | undefined {
  return TEMPLATES.find((t) => t.id === id);
}

function normalize(s: string): string {
  return s
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .trim();
}

/** Sucht in Name, Kategorie und Suchbegriffen. Leere Suche liefert alle Vorlagen. */
export function searchTemplates(query: string, category?: GameTemplate['category']): GameTemplate[] {
  const q = normalize(query);
  return TEMPLATES.filter((t) => {
    if (category && t.category !== category) return false;
    if (!q) return true;
    const hay = [t.name, t.category, ...t.keywords].map(normalize);
    return q.split(/\s+/).every((word) => hay.some((h) => h.includes(word)));
  });
}
