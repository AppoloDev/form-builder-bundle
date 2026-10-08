<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Exception;

/**
 * Un bloc de la structure porte un type qu'aucune classe Field ne gère.
 */
final class UnknownFieldTypeException extends \InvalidArgumentException
{
    public static function forBlock(string $type, ?string $blockId): self
    {
        return new self(\sprintf('Type de champ inconnu "%s" (bloc "%s") : aucune classe Field ne le gère.', $type, $blockId ?? '?'));
    }
}
