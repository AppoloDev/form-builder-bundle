<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\AddressType;

class Address extends AbstractSimpleValueField
{
    protected function getFormType(): string
    {
        return AddressType::class;
    }
}
