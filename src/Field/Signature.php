<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\SignatureType;

class Signature extends AbstractSimpleValueField
{
    protected function getFormType(): string
    {
        return SignatureType::class;
    }
}
