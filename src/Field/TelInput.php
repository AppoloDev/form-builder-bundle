<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Numéro de téléphone. Format et longueur configurables (`form_builder.tel.*`) : par défaut, chiffres avec un
 * `+` initial optionnel, de 6 à 15 caractères.
 */
class TelInput extends GenericTextInput
{
    protected string $inputType = TelType::class;
    protected ?string $mask = 'tel';

    /**
     * @param int<0, max> $minLength
     * @param int<1, max> $maxLength
     */
    public function __construct(
        #[Autowire(param: 'form_builder.tel_pattern')]
        private readonly string $pattern = '/^\+?[0-9]+$/',
        #[Autowire(param: 'form_builder.tel_min_length')]
        private readonly int $minLength = 6,
        #[Autowire(param: 'form_builder.tel_max_length')]
        private readonly int $maxLength = 15,
    ) {
    }

    /**
     * Le masque affiche « 06 12 34 56 78 » : séparateurs retirés avant validation et enregistrement (0612345678).
     */
    protected function afterAddField(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->get($this->id)->addModelTransformer(new CallbackTransformer(
            static fn (mixed $value): mixed => $value,
            static fn (mixed $value): mixed => \is_string($value) ? preg_replace('/[\s.\-]/', '', $value) : $value,
        ));
    }

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
                pattern: $this->pattern,
                message: 'validators.form_builder.tel_digits_only',
            ),
            new Length(
                min: $this->minLength,
                max: $this->maxLength,
                minMessage: 'validators.form_builder.tel_too_short',
                maxMessage: 'validators.form_builder.tel_too_long',
                exactMessage: 'validators.form_builder.tel_exact_length',
            ),
        ];

        return $fieldOptions;
    }
}
