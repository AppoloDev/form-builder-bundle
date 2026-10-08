<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\TitleType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;

class Title extends AbstractDisplayOnlyField
{
    private string $heading = TitleType::DEFAULT_HEADING;

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $valid = parent::validateDefinition($block, $formOptions);

        $heading = $block->configString('heading');
        $this->heading = null !== $heading && \in_array($heading, TitleType::HEADINGS, true) ? $heading : TitleType::DEFAULT_HEADING;

        return $valid;
    }

    protected function getFormType(): string
    {
        return TitleType::class;
    }

    protected function getFieldOptions(): array
    {
        return [...parent::getFieldOptions(), 'heading' => $this->heading];
    }
}
