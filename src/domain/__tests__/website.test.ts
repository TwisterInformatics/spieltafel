import { describe, expect, it } from '@jest/globals';

import { TEMPLATES } from '../templates';
// eslint-disable-next-line @typescript-eslint/no-require-imports
const websiteGames = require('../../../website/spieltafel/data/games.json') as {
  id: string;
  name: string;
  guide: string[];
  rules: { url: string; source: string } | null;
}[];

describe('Webseite', () => {
  it('hat dieselben Spiele, Kurzanleitungen und Links wie die App (sonst npm run website:data)', () => {
    expect(websiteGames.map((g) => ({ id: g.id, name: g.name, guide: g.guide, rules: g.rules }))).toEqual(
      TEMPLATES.map((t) => ({ id: t.id, name: t.name, guide: t.guide, rules: t.rules ?? null })),
    );
  });
});
