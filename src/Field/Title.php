<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\TitleType;

class Title extends AbstractDisplayOnlyField
{
    protected function getFormType(): string
    {
        return TitleType::class;
    }
}
