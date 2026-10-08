<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DatePickerType extends AbstractType
{
    public function getParent(): string
    {
        return DateType::class;
    }
}
