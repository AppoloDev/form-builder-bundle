<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Service;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;

/**
 * Source de vérité unique pour classer un type de champ (chaîne brute
 * issue de la structure JSON d'un FormLayout) selon son comportement
 * vis-à-vis de la valeur de réponse — remplace les switch/in_array sur
 * ['Title','Paragraph','FieldSet','Repeatable'] dupliqués à travers le
 * projet. `Core\FormBuilder\Field\FieldInterface::getKind()` délègue à
 * ce même resolver pour ne jamais diverger.
 */
final class FieldKindResolver
{
    public static function resolve(string $type): FieldKind
    {
        return match ($type) {
            'Title', 'Paragraph' => FieldKind::DisplayOnly,
            'FieldSet' => FieldKind::Container,
            'Repeatable' => FieldKind::Repeatable,
            default => FieldKind::Value,
        };
    }
}
