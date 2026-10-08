<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TextareaInput extends AbstractValueFieldWithDefault
{
    private int $rows = 5;

    protected function getFormType(): string
    {
        return TextareaType::class;
    }

    /**
     * @param array<string, mixed> $formOptions
     */
    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $valid = parent::validateDefinition($block, $formOptions);
        $this->rows = (int) ($block->configNumeric('rows') ?? 5);

        return $valid;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();
        $attr = \is_array($fieldOptions['attr']) ? $fieldOptions['attr'] : [];
        $attr['rows'] = $this->rows;
        $fieldOptions['attr'] = $attr;

        return $fieldOptions;
    }
}
