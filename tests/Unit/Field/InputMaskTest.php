<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Tests\Support\FormHarness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Masques de saisie (contrôleur form-builder-input-mask) des champs téléphone, e-mail et URL.
 */
class InputMaskTest extends TestCase
{
    #[DataProvider('maskedTypes')]
    public function testTheInputCarriesItsMask(string $type, string $mask): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => $type, 'label' => 'Champ']]);
        $attr = $form->get('f')->getConfig()->getOption('attr');

        self::assertIsArray($attr);
        self::assertSame('form-builder-input-mask', $attr['data-controller'] ?? null);
        self::assertSame($mask, $attr['data-form-builder-input-mask-type-value'] ?? null);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function maskedTypes(): iterable
    {
        yield 'tel' => ['TelInput', 'tel'];
        yield 'email' => ['EmailInput', 'email'];
        yield 'url' => ['UrlInput', 'url'];
    }

    public function testAPlainTextInputHasNoMask(): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => 'TextInput', 'label' => 'Champ']]);
        $attr = $form->get('f')->getConfig()->getOption('attr');

        self::assertIsArray($attr);
        self::assertArrayNotHasKey('data-controller', $attr);
    }

    #[DataProvider('formattedPhones')]
    public function testAFormattedPhoneIsStoredWithoutSeparators(string $submitted, string $stored): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => 'TelInput', 'label' => 'Téléphone']]);
        $form->submit(['f' => $submitted]);

        self::assertTrue($form->isValid(), implode(', ', FormHarness::errors($form)));
        self::assertSame($stored, $form->get('f')->getData());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function formattedPhones(): iterable
    {
        yield 'national' => ['06 12 34 56 78', '0612345678'];
        yield 'international' => ['+33 6 12 34 56 78', '+33612345678'];
        yield 'dots and dashes' => ['06.12-34.56.78', '0612345678'];
    }
}
