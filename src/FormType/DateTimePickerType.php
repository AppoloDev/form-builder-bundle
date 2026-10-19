<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Date + heure : conteneur `text` dont la date et l'heure sont deux champs natifs `single_text`
 * (`<input type="date">` + `<input type="time">`), pendant de DatePickerType.
 */
class DateTimePickerType extends AbstractType
{
    public function getParent(): string
    {
        return DateTimeType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'text',
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ]);
    }
}
