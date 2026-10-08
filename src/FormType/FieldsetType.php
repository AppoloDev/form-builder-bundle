<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldsetType extends AbstractChildrenFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'compound' => true,
        ]);
    }
}
