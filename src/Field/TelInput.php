<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class TelInput extends GenericTextInput
{
    protected string $inputType = TelType::class;

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();

        $constraints = $fieldOptions['constraints'] ?? [];
        $fieldOptions['constraints'] = [
            ...(\is_array($constraints) ? $constraints : []),
            new Regex(
                pattern: '/^(\+)?[0-9]\d*$/',
                message: 'validators.form_builder.tel_digits_only',
            ),
            new Length(
                min: 10,
                max: 10,
                minMessage: 'validators.form_builder.tel_exact_length',
                maxMessage: 'validators.form_builder.tel_exact_length',
            ),
        ];

        return $fieldOptions;
    }
}
