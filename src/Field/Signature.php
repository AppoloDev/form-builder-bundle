<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\FormType\SignatureType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class Signature extends AbstractSimpleValueField
{
    /** Taille maximale d'une signature (data URI) en caractères. */
    private const MAX_LENGTH = 1_000_000;

    protected function getFormType(): string
    {
        return SignatureType::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();

        // La valeur est affichée dans un <img src> (aussi dans les PDF) : seule une image en data URI est acceptée.
        $constraints = $fieldOptions['constraints'] ?? [];
        $fieldOptions['constraints'] = [
            ...(\is_array($constraints) ? $constraints : []),
            new Regex(
                pattern: '#^data:image/(png|jpeg|svg\+xml);base64,[A-Za-z0-9+/]+=*$#',
                message: 'validators.form_builder.signature_invalid',
            ),
            new Length(max: self::MAX_LENGTH, maxMessage: 'validators.form_builder.signature_too_long'),
        ];

        return $fieldOptions;
    }
}
