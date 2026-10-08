<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\AddressType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

class AddressInput extends AbstractSimpleValueField
{
    private string $placeholder = '';

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $valid = parent::validateDefinition($block, $formOptions);
        $this->placeholder = $block->configString('placeHolder') ?? '';

        return $valid;
    }

    protected function getFormType(): string
    {
        return AddressType::class;
    }

    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();

        if ('' !== $this->placeholder) {
            $attr = \is_array($fieldOptions['attr'] ?? null) ? $fieldOptions['attr'] : [];
            $attr['placeholder'] = $this->placeholder;
            $fieldOptions['attr'] = $attr;
        }

        return $fieldOptions;
    }
}
