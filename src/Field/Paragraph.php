<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\ParagraphType;

class Paragraph extends AbstractDisplayOnlyField
{
    protected function getFormType(): string
    {
        return ParagraphType::class;
    }
}
