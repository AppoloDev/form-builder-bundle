<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Service;

/**
 * Passage entre les deux représentations d'une structure de formulaire :
 *
 * - **imbriquée** (celle du builder) : un `Select` / `ChoiceGroup` porte `conditions`, une liste de règles
 *   `{id, operator, optionId, children: [blocs]}` ;
 * - **à plat** (celle que manipulent la base, le formulaire et les réponses) : les blocs conditionnels sont
 *   des frères du bloc propriétaire, marqués `condition: {owner, rule}`, et le propriétaire garde ses règles
 *   sans enfants. Les réponses restent ainsi au même niveau (y compris dans un Repeatable ou un FieldSet).
 *
 * `flatten()` est appliqué à l'entrée (FormLayoutBlock::listFromArray), `nest()` à la sortie (getStructure()).
 */
final class ConditionalStructure
{
    public const RULES_KEY = 'conditions';
    public const MARKER_KEY = 'condition';

    /**
     * @param array<mixed> $blocks
     *
     * @return list<array<string, mixed>>
     */
    public static function flatten(array $blocks): array
    {
        $flat = [];
        foreach ($blocks as $block) {
            if (\is_array($block)) {
                array_push($flat, ...self::flattenBlock($block));
            }
        }

        return $flat;
    }

    /**
     * @param array<array-key, mixed> $block
     *
     * @return list<array<string, mixed>>
     */
    private static function flattenBlock(array $block): array
    {
        $block = self::stringKeyed($block);

        if (\is_array($block['children'] ?? null)) {
            $block['children'] = self::flatten($block['children']);
        }

        $rules = $block[self::RULES_KEY] ?? null;
        if (!\is_array($rules) || [] === $rules) {
            return [$block];
        }

        $ownerId = \is_scalar($block['id'] ?? null) ? (string) $block['id'] : null;
        $meta = [];
        $conditional = [];

        foreach ($rules as $rule) {
            if (!\is_array($rule)) {
                continue;
            }

            $ruleId = \is_scalar($rule['id'] ?? null) ? (string) $rule['id'] : null;
            $meta[] = [
                'id' => $ruleId,
                'operator' => \is_string($rule['operator'] ?? null) ? $rule['operator'] : 'is',
                'optionId' => \is_scalar($rule['optionId'] ?? null) ? (string) $rule['optionId'] : null,
            ];

            if (null === $ownerId || null === $ruleId) {
                continue;
            }

            foreach (self::flatten(\is_array($rule['children'] ?? null) ? $rule['children'] : []) as $child) {
                // Un petit-enfant porte déjà le marqueur de son propre propriétaire.
                $child[self::MARKER_KEY] ??= ['owner' => $ownerId, 'rule' => $ruleId];
                $conditional[] = $child;
            }
        }

        $block[self::RULES_KEY] = $meta;

        return [$block, ...$conditional];
    }

    /**
     * @param array<mixed> $blocks
     *
     * @return list<array<string, mixed>>
     */
    public static function nest(array $blocks): array
    {
        /** @var array<string, array<string, list<array<string, mixed>>>> $conditional */
        $conditional = [];
        $plain = [];
        $ids = [];

        foreach ($blocks as $block) {
            if (!\is_array($block)) {
                continue;
            }

            $block = self::stringKeyed($block);
            if (\is_scalar($block['id'] ?? null)) {
                $ids[(string) $block['id']] = true;
            }

            $marker = self::marker($block);
            if (null === $marker) {
                $plain[] = $block;
            } else {
                $conditional[$marker['owner']][$marker['rule']][] = $block;
            }
        }

        $result = [];
        foreach ($plain as $block) {
            $result[] = self::nestBlock($block, $conditional);
        }

        // Blocs conditionnels dont le propriétaire est introuvable : conservés comme blocs ordinaires.
        foreach ($conditional as $ownerId => $rules) {
            if (isset($ids[$ownerId])) {
                continue;
            }
            foreach ($rules as $orphans) {
                foreach ($orphans as $orphan) {
                    unset($orphan[self::MARKER_KEY]);
                    $result[] = self::nestBlock($orphan, $conditional);
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed>                                        $block
     * @param array<string, array<string, list<array<string, mixed>>>> $conditional
     *
     * @return array<string, mixed>
     */
    private static function nestBlock(array $block, array $conditional): array
    {
        unset($block[self::MARKER_KEY]);

        if (\is_array($block['children'] ?? null)) {
            $block['children'] = self::nest($block['children']);
        }

        $rules = $block[self::RULES_KEY] ?? null;
        if (!\is_array($rules) || [] === $rules) {
            return $block;
        }

        $ownerId = \is_scalar($block['id'] ?? null) ? (string) $block['id'] : null;
        $nested = [];
        foreach ($rules as $rule) {
            if (!\is_array($rule)) {
                continue;
            }

            $ruleId = \is_scalar($rule['id'] ?? null) ? (string) $rule['id'] : null;
            $children = [];
            if (null !== $ownerId && null !== $ruleId) {
                foreach ($conditional[$ownerId][$ruleId] ?? [] as $child) {
                    $children[] = self::nestBlock($child, $conditional);
                }
            }

            $rule['children'] = $children;
            $nested[] = $rule;
        }

        $block[self::RULES_KEY] = $nested;

        return $block;
    }

    /**
     * @param array<mixed> $values
     *
     * @return array<string, mixed>
     */
    private static function stringKeyed(array $values): array
    {
        return array_filter($values, \is_string(...), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<array-key, mixed> $block
     *
     * @return array{owner: string, rule: string}|null
     */
    public static function marker(array $block): ?array
    {
        $marker = $block[self::MARKER_KEY] ?? null;
        if (!\is_array($marker) || !\is_scalar($marker['owner'] ?? null) || !\is_scalar($marker['rule'] ?? null)) {
            return null;
        }

        return ['owner' => (string) $marker['owner'], 'rule' => (string) $marker['rule']];
    }

    /**
     * Règle d'un bloc propriétaire (à plat) : opérateur et libellé de l'option visée.
     *
     * @param array<array-key, mixed> $ownerConfig config ou bloc du propriétaire (clés `conditions` et `options`)
     *
     * @return array{operator: string, optionLabel: string}|null
     */
    public static function rule(array $ownerConfig, string $ruleId): ?array
    {
        $rules = $ownerConfig[self::RULES_KEY] ?? null;
        $options = $ownerConfig['options'] ?? null;
        if (!\is_array($rules) || !\is_array($options)) {
            return null;
        }

        foreach ($rules as $rule) {
            if (!\is_array($rule) || !\is_scalar($rule['id'] ?? null) || (string) $rule['id'] !== $ruleId) {
                continue;
            }

            foreach ($options as $option) {
                if (\is_array($option) && \is_scalar($option['id'] ?? null) && \is_scalar($rule['optionId'] ?? null)
                    && (string) $option['id'] === (string) $rule['optionId'] && \is_string($option['label'] ?? null)) {
                    return [
                        'operator' => 'is_not' === ($rule['operator'] ?? null) ? 'is_not' : 'is',
                        'optionLabel' => $option['label'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Le bloc conditionnel est-il à afficher, d'après la valeur choisie dans le bloc propriétaire ?
     * Une règle introuvable (option supprimée...) masque le bloc.
     *
     * @param array<array-key, mixed> $ownerConfig
     */
    public static function isActive(array $ownerConfig, string $ruleId, mixed $ownerValue): bool
    {
        $rule = self::rule($ownerConfig, $ruleId);
        if (null === $rule) {
            return false;
        }

        $selected = \is_array($ownerValue)
            ? \in_array($rule['optionLabel'], $ownerValue, true)
            : $ownerValue === $rule['optionLabel'];

        return 'is_not' === $rule['operator'] ? !$selected : $selected;
    }
}
