import { describe, expect, it } from 'vitest';
import { completeMask, formatMask } from './masks';

describe('formatMask', () => {
    it.each([
        ['tel', '0612345678', '06 12 34 56 78'],
        ['tel', '06.12-34 56 78 99', '06 12 34 56 78'],
        ['tel', '061', '06 1'],
        ['tel', '+33612345678', '+33 6 12 34 56 78'],
        ['tel', '+33', '+33'],
        ['tel', '+', '+'],
        ['tel', '+32 470 12 34 56', '+32470123456'],
        ['tel', 'abc', ''],
        ['email', ' Jean.Dupont @Exemple.FR ', 'jean.dupont@exemple.fr'],
        ['url', 'www.exemple .fr', 'www.exemple.fr'],
    ])('%s %j => %j', (type, value, expected) => {
        expect(formatMask(type, value)).toBe(expected);
    });
});

describe('completeMask', () => {
    it.each([
        ['url', 'exemple.fr', 'https://exemple.fr'],
        ['url', 'http://exemple.fr', 'http://exemple.fr'],
        ['url', '', ''],
        ['tel', '0612345678', '06 12 34 56 78'],
    ])('%s %j => %j', (type, value, expected) => {
        expect(completeMask(type, value)).toBe(expected);
    });
});
