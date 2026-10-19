<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Tests\Support\FormHarness;
use PHPUnit\Framework\TestCase;

/**
 * `required` et `maxItems` du builder doivent tenir sans le JavaScript (POST direct).
 */
class ServerSideLimitsTest extends TestCase
{
    public function testARequiredFileFieldRejectsAnEmptySubmission(): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => 'FileInput', 'label' => 'Pièce', 'required' => true]]);
        $form->submit([]);

        self::assertFalse($form->isValid());
        self::assertSame('validators.form_builder.file_required', FormHarness::errors($form)[0] ?? null);
    }

    public function testAnOptionalFileFieldAcceptsAnEmptySubmission(): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => 'FileInput', 'label' => 'Pièce']]);
        $form->submit([]);

        self::assertTrue($form->isValid());
    }

    public function testFileFieldRejectsMoreThanMaxItems(): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => 'FileInput', 'label' => 'Pièce', 'maxItems' => 2]]);
        $form->submit(['f' => [[], [], []]]);

        self::assertFalse($form->isValid());
        self::assertSame('validators.form_builder.too_many_items', FormHarness::errors($form)[0] ?? null);
    }

    public function testSingleFileFieldRejectsTwoFiles(): void
    {
        $form = (new FormHarness())->form([['id' => 'f', 'type' => 'FileInput', 'label' => 'Pièce', 'allowMultiple' => false]]);
        $form->submit(['f' => [[], []]]);

        self::assertFalse($form->isValid());
    }

    public function testRepeatableRejectsMoreThanMaxItems(): void
    {
        $structure = [['id' => 'r', 'type' => 'Repeatable', 'maxItems' => 2, 'children' => [['id' => 't', 'type' => 'TextInput', 'label' => 'Nom']]]];

        $tooMany = (new FormHarness())->form($structure);
        $tooMany->submit(['r' => [['t' => 'a'], ['t' => 'b'], ['t' => 'c']]]);
        self::assertFalse($tooMany->isValid());

        $ok = (new FormHarness())->form($structure);
        $ok->submit(['r' => [['t' => 'a'], ['t' => 'b']]]);
        self::assertTrue($ok->isValid());
    }

    public function testRepeatableWithZeroMaxItemsIsUnlimited(): void
    {
        $form = (new FormHarness())->form([['id' => 'r', 'type' => 'Repeatable', 'maxItems' => 0, 'children' => [['id' => 't', 'type' => 'TextInput', 'label' => 'Nom']]]]);
        $form->submit(['r' => array_fill(0, 12, ['t' => 'x'])]);

        self::assertTrue($form->isValid());
    }
}
