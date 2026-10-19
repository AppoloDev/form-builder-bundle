<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\TimeType;

/**
 * Saisie d'une heure (HH:MM). La valeur est stockée sous forme de chaîne "HH:MM:SS" (secondes à 00), format du TimeType en entrée `string`.
 */
class HourMinuteInput extends AbstractValueFieldWithDefault
{
    private const TIME_PATTERN = '/^([01]\d|2[0-3]):[0-5]\d$/';

    protected function getFormType(): string
    {
        return TimeType::class;
    }

    protected function getFieldOptions(): array
    {
        $fieldOptions = parent::getFieldOptions();
        $fieldOptions['input'] = 'string';
        $fieldOptions['widget'] = 'single_text';
        $fieldOptions['with_seconds'] = false;

        if (1 !== preg_match(self::TIME_PATTERN, $this->defaultValue)) {
            unset($fieldOptions['data']);
        } elseif (isset($fieldOptions['data'])) {
            // TimeType (input `string`) lit et écrit "HH:MM:SS" : la valeur par défaut saisie en "HH:MM" doit suivre.
            $fieldOptions['data'] = $this->defaultValue.':00';
        }

        return $fieldOptions;
    }
}
