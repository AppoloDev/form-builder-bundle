<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleType extends AbstractType
{
    public const HEADINGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    /** Niveau des titres dont la structure ne précise pas `heading` (rendu historique). */
    public const DEFAULT_HEADING = 'h3';

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['heading'] = $options['heading'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['heading' => self::DEFAULT_HEADING]);
        $resolver->setAllowedValues('heading', self::HEADINGS);
    }
}
