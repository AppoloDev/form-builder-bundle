<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Tests\Support\FormHarness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * `readOnly` est rendu par `disabled` : le navigateur ne poste pas le champ et Symfony ignore toute valeur postée.
 * La valeur initiale (valeur par défaut à la création, réponse enregistrée en édition) doit malgré tout figurer dans
 * les données du formulaire, que l'hôte enregistre.
 */
class ReadOnlyFieldsTest extends TestCase
{
    /**
     * @param array<string, mixed> $block
     */
    #[DataProvider('readOnlyBlocks')]
    public function testReadOnlyDefaultValueSurvivesCreation(array $block, mixed $expected): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'label' => 'F', 'readOnly' => true, ...$block]]);
        $form->submit([]);

        self::assertTrue($form->get('f')->isDisabled());
        self::assertEquals($expected, $form->get('f')->getData());
        // C'est `getData()` du formulaire que l'hôte enregistre comme réponse.
        self::assertEquals(['f' => $expected], $form->getData());
    }

    public function testReadOnlyDefaultValueInFieldsetIsSaved(): void
    {
        $form = (new FormHarness())->form([['id' => 'g', 'type' => 'FieldSet', 'label' => 'G', 'children' => [
            ['id' => 'f', 'type' => 'TextInput', 'label' => 'F', 'readOnly' => true, 'defaultValue' => 'abc'],
        ]]]);
        $form->submit([]);

        self::assertEquals(['g' => ['f' => 'abc']], $form->getData());
    }

    /**
     * @param array<string, mixed> $block
     */
    #[DataProvider('readOnlyBlocks')]
    public function testReadOnlyValueSurvivesEditionAndIgnoresPostedValue(array $block, mixed $expected): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'label' => 'F', 'readOnly' => true, ...$block]], ['f' => $expected], ['edit' => true]);
        $form->submit(['f' => 'tampered']);

        self::assertEquals($expected, $form->get('f')->getData());
        self::assertEquals(['f' => $expected], $form->getData());
    }

    /**
     * @return iterable<string, array{array<string, mixed>, mixed}>
     */
    public static function readOnlyBlocks(): iterable
    {
        yield 'text' => [['type' => 'TextInput', 'defaultValue' => 'abc'], 'abc'];
        yield 'textarea' => [['type' => 'TextareaInput', 'defaultValue' => 'long'], 'long'];
        yield 'select' => [['type' => 'Select', 'options' => [['label' => 'A', 'isSelected' => true], ['label' => 'B', 'isSelected' => false]]], 'A'];
        yield 'radios' => [['type' => 'ChoiceGroup', 'options' => [['label' => 'A', 'isSelected' => true], ['label' => 'B', 'isSelected' => false]]], 'A'];
    }
}
