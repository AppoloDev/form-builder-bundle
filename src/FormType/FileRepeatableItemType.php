<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileRepeatableItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<string, mixed> $fileOptions */
        $fileOptions = $options['file_options'] ?? [];

        // Libellé saisi par l'utilisateur : non traduit ; à défaut, libellé par défaut du bundle.
        $customLabel = \is_string($fileOptions['label'] ?? null) && '' !== $fileOptions['label'];

        $builder->add('file', FileType::class, [
            'label' => $customLabel ? $fileOptions['label'] : 'answers.file_label',
            'translation_domain' => $customLabel ? false : 'form_builder_bundle',
            'label_attr' => $fileOptions['label_attr'] ?? [],
            'help' => $fileOptions['help'] ?? null,
            'required' => $fileOptions['required'] ?? false,
            'data_class' => null,
            'constraints' => $fileOptions['constraints'] ?? [],
        ]);
        $builder->add('delete', CheckboxType::class, [
            'attr' => [
                'class' => 'input-delete',
            ],
            'required' => false,
            'row_attr' => [
                'class' => 'd-none',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'file_options' => [],
            'translation_domain' => false,
        ]);

        $resolver->setAllowedTypes('file_options', ['array']);
    }
}
