<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;

class EmailInput extends GenericTextInput
{
    protected string $inputType = EmailType::class;

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();

        $constraints = $fieldOptions['constraints'] ?? [];
        $fieldOptions['constraints'] = [
            ...(\is_array($constraints) ? $constraints : []),
            new Email(),
        ];

        return $fieldOptions;
    }
}
