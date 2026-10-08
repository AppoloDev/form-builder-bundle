<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Answer;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Service\ConditionalStructure;
use AppoloDev\FormBuilderBundle\Service\FieldKindResolver;

class AnswerGenerator
{
    /**
     * @param array<int, array<mixed, mixed>> $formBuilderContent
     * @param array<mixed, mixed>             $formAnswerContent
     *
     * @return array<int, array<mixed, mixed>>
     */
    public function generate(array $formBuilderContent, array $formAnswerContent): array
    {
        // Les blocs conditionnels deviennent des frères de leur propriétaire ; ceux dont la règle n'est
        // pas satisfaite par la réponse sont ignorés.
        return $this->generateLevel(ConditionalStructure::flatten($formBuilderContent), $formAnswerContent);
    }

    /**
     * @param array<int, array<mixed, mixed>> $blocks  blocs d'un même niveau, structure à plat
     * @param array<mixed, mixed>             $content réponses de ce niveau
     *
     * @return array<int, array<mixed, mixed>>
     */
    private function generateLevel(array $blocks, array $content): array
    {
        return array_values(array_map(
            fn (array $item): array => $this->generateItem($item, $content),
            $this->activeBlocks($blocks, $content),
        ));
    }

    /**
     * @param array<int, array<mixed, mixed>> $blocks
     * @param array<mixed, mixed>             $content
     *
     * @return array<int, array<mixed, mixed>>
     */
    private function activeBlocks(array $blocks, array $content): array
    {
        $byId = [];
        foreach ($blocks as $block) {
            if (\is_string($block['id'] ?? null)) {
                $byId[$block['id']] = $block;
            }
        }

        return array_values(array_filter($blocks, static function (array $block) use ($byId, $content): bool {
            $marker = ConditionalStructure::marker($block);
            if (null === $marker) {
                return true;
            }

            $owner = $byId[$marker['owner']] ?? null;

            return null !== $owner && ConditionalStructure::isActive($owner, $marker['rule'], $content[$marker['owner']] ?? null);
        }));
    }

    /**
     * @param array<mixed, mixed> $item
     * @param array<mixed, mixed> $formAnswerContent
     *
     * @return array<mixed, mixed>
     */
    private function generateItem(array $item, array $formAnswerContent): array
    {
        $id = $item['id'] ?? null;
        $children = $this->toArrayList($item['children'] ?? null);

        if ([] !== $children && \is_string($id) && isset($formAnswerContent[$id])) {
            $answerChildren = $formAnswerContent[$id];
            $item['children'] = $this->generateLevel($children, \is_array($answerChildren) ? $answerChildren : []);
        }

        $type = $item['type'] ?? '';
        $kind = FieldKindResolver::resolve(\is_string($type) ? $type : '');

        if (FieldKind::Repeatable === $kind && \is_string($id)) {
            $answerValue = $formAnswerContent[$id] ?? [];
            $item['value'] = $this->getRepeatableValues($children, \is_array($answerValue) ? $answerValue : []);
        } elseif (FieldKind::Value === $kind && \is_string($id)) {
            $item['value'] = $formAnswerContent[$id] ?? null;
        }

        return $item;
    }

    /**
     * @param array<int, array<mixed, mixed>> $answers
     *
     * @return array<int, array{label: mixed, value: mixed}>
     */
    public function flattenToFields(array $answers): array
    {
        $fields = [];
        foreach ($answers as $item) {
            $type = $item['type'] ?? '';
            $kind = FieldKindResolver::resolve(\is_string($type) ? $type : '');

            if (FieldKind::DisplayOnly === $kind) {
                continue;
            }
            if (FieldKind::Container === $kind) {
                $fields = array_merge($fields, $this->flattenToFields($this->toArrayList($item['children'] ?? null)));
                continue;
            }
            if (FieldKind::Repeatable === $kind) {
                /** @var array<int, array<int|string, array{type?: string, label?: string, value?: mixed}>> $rows */
                $rows = $item['value'] ?? [];
                if ([] === $rows) {
                    continue;
                }
                foreach (array_keys($rows[0]) as $i) {
                    $childType = $rows[0][$i]['type'] ?? '';
                    $label = $rows[0][$i]['label'] ?? '';
                    if (FieldKind::DisplayOnly === FieldKindResolver::resolve($childType) || '' === $label) {
                        continue;
                    }
                    $values = array_filter(
                        array_map(static function (array $row) use ($i): string {
                            $val = $row[$i]['value'] ?? '';

                            return \is_array($val) ? implode(', ', array_map(static fn (mixed $v): string => \is_scalar($v) ? (string) $v : '', $val)) : (\is_scalar($val) ? (string) $val : '');
                        }, $rows),
                        static fn (string $v): bool => '' !== $v
                    );
                    $fields[] = ['label' => $label, 'value' => implode(' - ', $values)];
                }
                continue;
            }
            $fields[] = ['label' => $item['label'] ?? '', 'value' => $item['value'] ?? null];
        }

        return $fields;
    }

    /**
     * @param array<int, array<mixed, mixed>> $repeatableDefinition
     * @param array<mixed, mixed>             $repeatableValues
     *
     * @return array<mixed, array<int, array<mixed, mixed>>>
     */
    private function getRepeatableValues(array $repeatableDefinition, array $repeatableValues): array
    {
        return array_map(function (mixed $values) use ($repeatableDefinition): array {
            $values = \is_array($values) ? $values : [];

            return array_map(static function (array $field) use ($values): array {
                $id = $field['id'] ?? null;
                $field['value'] = \is_string($id) ? ($values[$id] ?? null) : null;

                return $field;
            }, $this->activeBlocks($repeatableDefinition, $values));
        }, $repeatableValues);
    }

    /**
     * @return array<int, array<mixed, mixed>>
     */
    private function toArrayList(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_array'));
    }
}
