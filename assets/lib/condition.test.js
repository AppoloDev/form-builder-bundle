import { describe, expect, it } from 'vitest';
import { isConditionActive } from './condition';

describe('isConditionActive', () => {
    it.each([
        [['Oui'], 'is', 'Oui', true],
        [['Non'], 'is', 'Oui', false],
        [[], 'is', 'Oui', false],
        [['Oui', 'Non'], 'is', 'Oui', true],
        [['Non'], 'is_not', 'Non', false],
        [['Oui'], 'is_not', 'Non', true],
        [[], 'is_not', 'Non', true],
    ])('selected=%j %s %s => %s', (selected, operator, label, expected) => {
        expect(isConditionActive(selected, operator, label)).toBe(expected);
    });
});
