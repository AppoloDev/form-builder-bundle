<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

/**
 * Choix affichés en radios (choix unique) ou en cases à cocher (`multiple`).
 */
class ChoiceGroup extends AbstractChoiceField
{
    protected function isExpanded(): bool
    {
        return true;
    }
}
