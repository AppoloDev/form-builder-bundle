<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Champ qui affiche le builder de formulaire (composant JS `form-builder`), à la manière d'un
 * TextType qui affiche un <input> : la donnée du champ est la structure (liste de blocs),
 * transformée en JSON dans le champ caché mis à jour par le builder.
 */
class FormStructureType extends AbstractType
{
    public function __construct(private readonly ?RequestStack $requestStack = null)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static fn (?array $structure): string => json_encode($structure ?? [], \JSON_THROW_ON_ERROR),
            static function (?string $json): ?array {
                if (null === $json || '' === $json) {
                    return null;
                }

                $structure = json_decode($json, true);

                return \is_array($structure) ? $structure : null;
            },
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['answers_count'] = $options['answers_count'];
        $view->vars['builder_locale'] = $options['locale'] ?? $this->requestStack?->getCurrentRequest()?->getLocale() ?? 'en';
        $data = $form->getData();
        $view->vars['has_structure'] = \is_array($data) && [] !== $data;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'answers_count' => 0,
            'locale' => null,
            'translation_domain' => 'form_builder_bundle',
        ]);
        $resolver->setAllowedTypes('answers_count', 'int');
        $resolver->setAllowedTypes('locale', ['null', 'string']);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_structure';
    }
}
