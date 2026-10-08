<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Service;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlockTreeWalker;

/**
 * Construction/reconstruction de l'arbre FormLayoutField <-> FormLayoutBlock — extrait de
 * FormLayout (qui ne devrait pas porter cette logique de traversée récursive) vers un
 * service dédié, à l'image de FormLayoutBlockTreeWalker déjà extrait en 2.2.
 */
final class FormLayoutFieldHydrator
{
    /**
     * @param FormLayoutBlock[] $blocks
     */
    public static function hydrate(FormLayoutInterface $formLayout, array $blocks, ?FormLayoutFieldInterface $parent = null): void
    {
        foreach ($blocks as $index => $block) {
            if (null === $block->id || null === $block->type) {
                continue;
            }

            $field = $formLayout->createField()
                ->setFormLayout($formLayout)
                ->setPosition((int) $index)
                ->setFieldKey($block->id)
                ->setType($block->type)
                ->setLabel($block->label)
                ->setText($block->text)
                ->setConfig($block->config);

            $formLayout->addField($field);

            // addChild() (et non setParent()) : synchronise aussi la collection children du
            // parent, pas seulement le pointeur parent de l'enfant — sans ça, getStructure()/
            // getRepeatableFieldMap() appelés sur le même FormLayout juste après hydrate()
            // (sans aller-retour base, ex. FormLayout::__clone()) ne retrouvent aucun enfant :
            // Doctrine ne synchronise le côté inverse de la relation qu'au rechargement.
            if ($parent instanceof FormLayoutFieldInterface) {
                $parent->addChild($field);
            }

            if ([] !== $block->children) {
                self::hydrate($formLayout, $block->children, $field);
            }
        }
    }

    /**
     * @param array<int, FormLayoutFieldInterface> $fields
     *
     * @return array<int, array<string, mixed>>
     */
    public static function buildContent(array $fields): array
    {
        usort($fields, fn (FormLayoutFieldInterface $a, FormLayoutFieldInterface $b): int => $a->getPosition() <=> $b->getPosition());

        $content = [];
        foreach ($fields as $field) {
            $block = array_merge([
                'id' => $field->getFieldKey(),
                'type' => $field->getType(),
            ], $field->getConfig());

            if (!is_null($field->getLabel())) {
                $block['label'] = $field->getLabel();
            }

            if (!is_null($field->getText())) {
                $block['text'] = $field->getText();
            }

            $children = self::buildContent($field->getChildren()->toArray());
            if ([] !== $children) {
                $block['children'] = $children;
            }

            $content[] = $block;
        }

        return $content;
    }

    /**
     * @param FormLayoutBlock[]       $blocks
     * @param array<string, string[]> &$repeatables
     */
    public static function collectRepeatables(array $blocks, array &$repeatables): void
    {
        FormLayoutBlockTreeWalker::walk($blocks, function (FormLayoutBlock $block) use (&$repeatables): void {
            if (FieldKind::Repeatable === FieldKindResolver::resolve((string) $block->type) && null !== $block->id) {
                $repeatables[$block->id] = array_values(array_filter(array_map(
                    fn (FormLayoutBlock $child): ?string => $child->id,
                    $block->children
                )));
            }
        });
    }
}
