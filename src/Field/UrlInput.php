<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\Url;

class UrlInput extends GenericTextInput
{
    protected string $inputType = UrlType::class;

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();

        $fieldOptions['default_protocol'] = 'https';

        $constraints = $fieldOptions['constraints'] ?? [];
        $fieldOptions['constraints'] = [
            ...(\is_array($constraints) ? $constraints : []),
            new Url(),
        ];

        return $fieldOptions;
    }
}
