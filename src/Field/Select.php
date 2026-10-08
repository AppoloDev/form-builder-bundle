<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

/**
 * Liste déroulante (autocomplétion), à choix unique ou multiple. `checkCases` (ancienne config) l'affiche
 * en radios / cases : le builder propose désormais le bloc ChoiceGroup pour cela.
 */
class Select extends AbstractChoiceField
{
    private bool $checkCases = false;
    private bool $customOption = false;

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $valid = parent::validateDefinition($block, $formOptions);
        $this->checkCases = $block->configBool('checkCases') ?? false;
        $this->customOption = $block->configBool('customOption') ?? false;

        return $valid;
    }

    protected function isExpanded(): bool
    {
        return $this->checkCases;
    }

    protected function decorateFieldOptions(array $fieldOptions): array
    {
        if (!$this->checkCases) {
            $fieldOptions['autocomplete'] = true;
            $fieldOptions['allow_options_create'] = $this->customOption;
        }

        return $fieldOptions;
    }
}
