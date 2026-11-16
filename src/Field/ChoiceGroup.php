<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

/**
 * Choix affichés en radios (choix unique) ou en cases à cocher (`multiple`).
 */
class ChoiceGroup extends AbstractChoiceField
{
    protected function readChoices(FormLayoutBlock $block): void
    {
        $this->options = $block->configOptions('options');
        $this->multiple = $block->configBool('multiple') ?? false;
    }

    protected function isExpanded(): bool
    {
        return true;
    }
}
