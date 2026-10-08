<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use AppoloDev\FormBuilderBundle\Form\FormTypeGenerator;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base commune aux types de formulaire compound dont les champs sont générés
 * dynamiquement depuis une option `children` (blocs de `FormLayoutBlock`).
 */
abstract class AbstractChildrenFormType extends AbstractType
{
    public function __construct(protected readonly FormTypeGenerator $formTypeGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $children = is_array($options['children']) ? FormLayoutBlock::filterBlocks($options['children']) : [];
        $this->formTypeGenerator->addFields($builder, $children);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'children' => [],
        ]);

        $resolver->setAllowedTypes('children', ['array']);
    }
}
