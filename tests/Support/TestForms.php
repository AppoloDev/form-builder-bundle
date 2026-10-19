<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;
use Symfony\UX\Autocomplete\Checksum\ChecksumCalculator;
use Symfony\UX\Autocomplete\Form\AutocompleteChoiceTypeExtension;

/**
 * Fabrique de formulaires Symfony avec l'extension de validation (option `constraints`).
 */
final class TestForms
{
    /**
     * @param list<\Symfony\Component\Form\FormTypeInterface> $types
     */
    public static function factory(array $types = []): FormFactoryInterface
    {
        $builder = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addTypes($types);

        // Dépendance de développement : fournit les options `autocomplete` / `allow_options_create` du Select.
        if (class_exists(AutocompleteChoiceTypeExtension::class)) {
            $builder->addTypeExtension(new AutocompleteChoiceTypeExtension(new ChecksumCalculator('test-secret')));
        }

        return $builder->getFormFactory();
    }
}
