<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeInterface;

class GenericTextInput extends AbstractValueFieldWithDefault
{
    /** @var class-string<FormTypeInterface> */
    protected string $inputType = TextType::class;

    /** Masque de saisie (contrôleur form-builder-input-mask) : `tel`, `email` ou `url`. */
    protected ?string $mask = null;

    protected function getFormType(): string
    {
        return $this->inputType;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();

        if (null !== $this->mask) {
            $attr = \is_array($fieldOptions['attr'] ?? null) ? $fieldOptions['attr'] : [];
            $fieldOptions['attr'] = [
                ...$attr,
                'data-controller' => 'form-builder-input-mask',
                'data-form-builder-input-mask-type-value' => $this->mask,
                'data-action' => 'input->form-builder-input-mask#format blur->form-builder-input-mask#complete',
            ];
        }

        return $fieldOptions;
    }
}
