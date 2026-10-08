<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeInterface;

class GenericTextInput extends AbstractValueFieldWithDefault
{
    /** @var class-string<FormTypeInterface> */
    protected string $inputType = TextType::class;

    protected function getFormType(): string
    {
        return $this->inputType;
    }
}
