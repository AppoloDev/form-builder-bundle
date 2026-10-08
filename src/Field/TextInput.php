<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class TextInput extends GenericTextInput
{
    protected string $inputType = TextType::class;
}
