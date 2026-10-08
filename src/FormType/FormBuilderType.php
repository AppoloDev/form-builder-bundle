<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormBuilderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'edit' => false,
            // Les libellés viennent de la structure saisie par l'utilisateur : ils ne se traduisent pas.
            'translation_domain' => false,
        ]);

        $resolver->setAllowedTypes('edit', ['bool']);
    }

    public function getParent(): string
    {
        return FormType::class;
    }
}
