<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\ValueObject;

/**
 * Représentation typée d'un noeud du schéma JSON stocké dans
 * `FormLayout::structure` — un champ simple, un bloc structurel
 * (FieldSet, Repeatable) ou un élément de présentation (Title, Paragraph).
 *
 * Centralise le parsing du JSON brut, jusque-là re-décodé indépendamment
 * (et avec des interprétations légèrement différentes) dans
 * FormTypeGenerator, FormLayoutStructureSyncService et
 * ExportFormAnswerController.
 */
final readonly class FormLayoutBlock
{
    /**
     * @param FormLayoutBlock[]    $children
     * @param array<string, mixed> $config   clés restantes du bloc brut, hors id/type/label/text/children
     */
    public function __construct(
        public ?string $id,
        public ?string $type,
        public ?string $label,
        public ?string $text,
        public array $children,
        public array $config,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $children = [];
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                if (is_array($child)) {
                    $children[] = self::fromArray($child);
                }
            }
        }

        $config = $data;
        unset($config['id'], $config['type'], $config['label'], $config['text'], $config['children']);

        return new self(
            id: is_scalar($data['id'] ?? null) ? (string) $data['id'] : null,
            type: is_scalar($data['type'] ?? null) ? (string) $data['type'] : null,
            label: is_scalar($data['label'] ?? null) ? (string) $data['label'] : null,
            text: is_scalar($data['text'] ?? null) ? (string) $data['text'] : null,
            children: $children,
            config: $config,
        );
    }

    /**
     * @param array<mixed> $blocks
     *
     * @return FormLayoutBlock[]
     */
    public static function listFromArray(array $blocks): array
    {
        $result = [];
        foreach ($blocks as $block) {
            if (is_array($block)) {
                $result[] = self::fromArray($block);
            }
        }

        return $result;
    }

    /**
     * @param array<mixed> $values
     *
     * @return FormLayoutBlock[]
     */
    public static function filterBlocks(array $values): array
    {
        return array_values(array_filter($values, fn (mixed $value): bool => $value instanceof self));
    }

    public function configString(string $key): ?string
    {
        $value = $this->config[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function configBool(string $key): ?bool
    {
        $value = $this->config[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    public function configNumeric(string $key): int|float|null
    {
        $value = $this->config[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return is_string($value) && is_numeric($value) ? (float) $value : null;
    }

    /**
     * @return array<int, array{label: string, isSelected: bool}>
     */
    public function configOptions(string $key): array
    {
        $value = $this->config[$key] ?? null;
        if (!is_array($value)) {
            return [];
        }

        $options = [];
        foreach ($value as $option) {
            if (is_array($option) && is_string($option['label'] ?? null) && is_bool($option['isSelected'] ?? null)) {
                $options[] = ['label' => $option['label'], 'isSelected' => $option['isSelected']];
            }
        }

        return $options;
    }
}
