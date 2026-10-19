<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Date saisie dans un seul champ `<input type="date">` (pendant de DateTimePickerType et du TimeType
 * `single_text` de DateTimeInput). `DateType` seul rendrait trois listes jour/mois/année.
 */
class DatePickerType extends AbstractType
{
    public function getParent(): string
    {
        return DateType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => true,
        ]);
    }
}
