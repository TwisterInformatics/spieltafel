import { describe, expect, it } from '@jest/globals';

import { TEMPLATES } from '../templates';
import websiteGames from '../../../website/spieltafel/data/games.json';

describe('Webseite', () => {
  it('hat dieselben Spiele, Kurzanleitungen und Links wie die App (sonst npm run website:data)', () => {
    expect(websiteGames.map((g) => ({ id: g.id, name: g.name, guide: g.guide, rules: g.rules }))).toEqual(
      TEMPLATES.map((t) => ({ id: t.id, name: t.name, guide: t.guide, rules: t.rules ?? null })),
    );
  });
});
