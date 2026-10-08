<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\ValueObject;

use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;

class FormLayoutBlockTest extends TestCase
{
    public function testFromArrayExtractsIdTypeLabelText(): void
    {
        $block = FormLayoutBlock::fromArray([
            'id' => 'field-a',
            'type' => 'ShortText',
            'label' => 'Label',
            'text' => 'Texte',
        ]);

        self::assertSame('field-a', $block->id);
        self::assertSame('ShortText', $block->type);
        self::assertSame('Label', $block->label);
        self::assertSame('Texte', $block->text);
    }

    public function testFromArrayCastsScalarIdToString(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 42, 'type' => 'ShortText']);

        self::assertSame('42', $block->id);
    }

    public function testFromArrayLeavesIdNullWhenNotScalar(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => ['not', 'scalar'], 'type' => 'ShortText']);

        self::assertNull($block->id);
    }

    public function testFromArrayParsesNestedChildrenRecursively(): void
    {
        $block = FormLayoutBlock::fromArray([
            'id' => 'group',
            'type' => 'Repeatable',
            'children' => [
                ['id' => 'child-a', 'type' => 'ShortText'],
                ['id' => 'child-b', 'type' => 'ShortText'],
            ],
        ]);

        self::assertCount(2, $block->children);
        self::assertSame('child-a', $block->children[0]->id);
        self::assertSame('child-b', $block->children[1]->id);
    }

    public function testFromArrayPutsUnknownKeysInConfig(): void
    {
        $block = FormLayoutBlock::fromArray([
            'id' => 'field-a',
            'type' => 'ShortText',
            'placeholder' => 'Saisir...',
            'required' => true,
        ]);

        self::assertSame(['placeholder' => 'Saisir...', 'required' => true], $block->config);
    }

    public function testListFromArraySkipsNonArrayEntries(): void
    {
        $blocks = FormLayoutBlock::listFromArray([
            ['id' => 'a', 'type' => 'ShortText'],
            'not-an-array',
            ['id' => 'b', 'type' => 'ShortText'],
        ]);

        self::assertCount(2, $blocks);
    }

    public function testFilterBlocksKeepsOnlyFormLayoutBlockInstances(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText']);

        $filtered = FormLayoutBlock::filterBlocks([$block, 'not-a-block', 42]);

        self::assertSame([$block], $filtered);
    }

    public function testConfigStringReturnsValueWhenItIsAString(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'placeholder' => 'Saisir...']);

        self::assertSame('Saisir...', $block->configString('placeholder'));
    }

    public function testConfigStringReturnsNullWhenMissingOrNotAString(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'required' => true]);

        self::assertNull($block->configString('placeholder'));
        self::assertNull($block->configString('required'));
    }

    public function testConfigBoolReturnsValueWhenItIsABool(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'required' => true]);

        self::assertTrue($block->configBool('required'));
    }

    public function testConfigBoolReturnsNullWhenMissingOrNotABool(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'placeholder' => 'x']);

        self::assertNull($block->configBool('required'));
    }

    public function testConfigNumericReturnsIntOrFloatAsIs(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'min' => 3, 'step' => 0.5]);

        self::assertSame(3, $block->configNumeric('min'));
        self::assertSame(0.5, $block->configNumeric('step'));
    }

    public function testConfigNumericCastsNumericStrings(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'min' => '3']);

        self::assertSame(3.0, $block->configNumeric('min'));
    }

    public function testConfigNumericReturnsNullWhenNotNumeric(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'ShortText', 'min' => 'abc']);

        self::assertNull($block->configNumeric('min'));
    }

    public function testConfigOptionsReturnsValidOptionsOnly(): void
    {
        $block = FormLayoutBlock::fromArray([
            'id' => 'a',
            'type' => 'Select',
            'options' => [
                ['label' => 'Oui', 'isSelected' => true],
                ['label' => 'Non', 'isSelected' => false],
                ['not-a-valid-option'],
                'not-an-array',
            ],
        ]);

        self::assertSame([
            ['label' => 'Oui', 'isSelected' => true],
            ['label' => 'Non', 'isSelected' => false],
        ], $block->configOptions('options'));
    }

    public function testConfigOptionsReturnsEmptyArrayWhenNotAnArray(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'a', 'type' => 'Select']);

        self::assertSame([], $block->configOptions('options'));
    }
}
