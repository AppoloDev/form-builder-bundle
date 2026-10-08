<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\FieldInterface;
use AppoloDev\FormBuilderBundle\Service\FieldKindResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Garde-fou contre la divergence déjà observée dans l'audit (liste
 * incomplète dans ResourceAnswer.php) : chaque classe Field/* doit
 * annoncer le même FieldKind que FieldKindResolver pour son propre
 * type, puisque ce dernier est la source de vérité utilisée côté
 * Domain (FormLayout, FormAnswer) là où une instance Field n'est pas
 * disponible.
 */
class FieldKindConsistencyTest extends TestCase
{
    #[DataProvider('typeProvider')]
    public function testFieldClassKindMatchesResolver(string $type): void
    {
        $className = 'AppoloDev\\FormBuilderBundle\\Field\\'.$type;
        self::assertTrue(class_exists($className), "Aucune classe Field pour le type '{$type}'.");

        $field = new $className();
        self::assertInstanceOf(FieldInterface::class, $field);

        self::assertSame(
            FieldKindResolver::resolve($type),
            $field->getKind(),
            "Le FieldKind de la classe '{$type}' diverge de FieldKindResolver."
        );
    }

    public static function typeProvider(): iterable
    {
        foreach ([
            'Address', 'DateTimeInput', 'EmailInput', 'FieldSet', 'FileInput',
            'GenericTextInput', 'NumberInput', 'Paragraph', 'Repeatable',
            'Select', 'Signature', 'TelInput', 'TextAreaInput', 'TextInput',
            'Title', 'UrlInput',
        ] as $type) {
            yield $type => [$type];
        }
    }
}
