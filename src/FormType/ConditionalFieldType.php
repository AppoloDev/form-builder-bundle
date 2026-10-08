<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use AppoloDev\FormBuilderBundle\Form\FormTypeGenerator;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enveloppe d'un champ conditionnel (affiché selon la valeur choisie dans un `Select` / `ChoiceGroup`).
 * Les données sont héritées du formulaire parent (`inherit_data`) : la valeur du champ reste au même niveau
 * que celle de son propriétaire. Le thème rend l'enveloppe avec les attributs lus par le contrôleur Stimulus
 * `form-builder-condition`, qui affiche ou masque le champ.
 */
class ConditionalFieldType extends AbstractType
{
    public function __construct(private readonly FormTypeGenerator $formTypeGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $block = $options['block'];
        \assert($block instanceof FormLayoutBlock);

        // Un champ conditionnel n'est jamais obligatoire : masqué, il ne doit pas bloquer l'envoi.
        $config = $block->config;
        unset($config['condition']);
        $config['required'] = false;

        $this->formTypeGenerator->addField($builder, new FormLayoutBlock(
            id: $block->id,
            type: $block->type,
            label: $block->label,
            text: $block->text,
            children: $block->children,
            config: $config,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $ownerId = \is_string($options['owner']) ? $options['owner'] : '';
        $parentId = null !== $view->parent && \is_string($view->parent->vars['id'] ?? null) ? $view->parent->vars['id'] : null;

        $view->vars['owner_id'] = null !== $parentId ? $parentId.'_'.$ownerId : $ownerId;
        $view->vars['operator'] = $options['operator'];
        $view->vars['option_label'] = $options['option_label'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['block', 'owner', 'operator', 'option_label']);
        $resolver->setAllowedTypes('block', FormLayoutBlock::class);
        $resolver->setAllowedTypes('owner', 'string');
        $resolver->setAllowedValues('operator', ['is', 'is_not']);
        $resolver->setAllowedTypes('option_label', 'string');
        $resolver->setDefaults([
            'inherit_data' => true,
            'label' => false,
            'required' => false,
            'translation_domain' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_conditional_field';
    }
}
