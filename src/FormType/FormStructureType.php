<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use AppoloDev\FormBuilderBundle\Enum\FieldCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Champ qui affiche le builder de formulaire (composant JS `form-builder`), à la manière d'un
 * TextType qui affiche un <input> : la donnée du champ est la structure (liste de blocs),
 * transformée en JSON dans le champ caché mis à jour par le builder.
 *
 * `categories` / `types` restreignent les champs que le builder propose À L'AJOUT (union des deux ; `null` =
 * tout). Les blocs déjà présents dans la structure restent affichés, modifiables et supprimables.
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
        $view->vars['allowed_categories'] = $options['categories'];
        $view->vars['allowed_types'] = $options['types'];
        $view->vars['builder_locale'] = $options['locale'] ?? $this->requestStack?->getCurrentRequest()?->getLocale() ?? 'en';
        $data = $form->getData();
        $view->vars['has_structure'] = \is_array($data) && [] !== $data;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'answers_count' => 0,
            'locale' => null,
            'categories' => null,
            'types' => null,
            'translation_domain' => 'form_builder_bundle',
        ]);
        $resolver->setAllowedTypes('answers_count', 'int');
        $resolver->setAllowedTypes('locale', ['null', 'string']);
        $resolver->setAllowedTypes('categories', ['null', 'string[]']);
        $resolver->setAllowedTypes('types', ['null', 'string[]']);
        $resolver->setNormalizer('categories', static fn (Options $options, ?array $categories): ?array => self::checkList('categories', $categories, FieldCategory::values()));
        $resolver->setNormalizer('types', static fn (Options $options, ?array $types): ?array => self::checkList('types', $types, FieldCategory::allTypes()));
    }

    /**
     * @param array<mixed>|null $values déjà validé comme liste de chaînes par setAllowedTypes
     * @param list<string>      $known
     *
     * @return list<string>|null
     */
    private static function checkList(string $option, ?array $values, array $known): ?array
    {
        if (null === $values) {
            return null;
        }

        $values = array_values(array_unique(array_filter($values, \is_string(...))));
        if ([] === $values) {
            throw new InvalidOptionsException(\sprintf('The "%s" option of FormStructureType cannot be empty: use null to offer everything.', $option));
        }

        $unknown = array_values(array_diff($values, $known));
        if ([] !== $unknown) {
            throw new InvalidOptionsException(\sprintf('Unknown value(s) %s in the "%s" option of FormStructureType. Allowed: %s.', implode(', ', array_map(static fn (string $v): string => '"'.$v.'"', $unknown)), $option, implode(', ', $known)));
        }

        return $values;
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
