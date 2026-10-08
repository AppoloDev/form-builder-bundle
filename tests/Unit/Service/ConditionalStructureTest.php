<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Service;

use AppoloDev\FormBuilderBundle\Service\ConditionalStructure;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use PHPUnit\Framework\TestCase;

class ConditionalStructureTest extends TestCase
{
    public function testFlattenTurnsConditionalChildrenIntoMarkedSiblingsAfterTheirOwner(): void
    {
        $flat = ConditionalStructure::flatten([self::nestedSelect(), ['id' => 'after', 'type' => 'TextInput']]);

        self::assertSame(['sel', 'child-a', 'child-b', 'after'], array_column($flat, 'id'));
        self::assertSame([['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1'], ['id' => 'r2', 'operator' => 'is_not', 'optionId' => 'o2']], $flat[0]['conditions']);
        self::assertSame(['owner' => 'sel', 'rule' => 'r1'], $flat[1]['condition']);
        self::assertSame(['owner' => 'sel', 'rule' => 'r2'], $flat[2]['condition']);
        self::assertArrayNotHasKey('condition', $flat[3]);
    }

    public function testNestRestoresTheBuilderStructure(): void
    {
        $nested = [self::nestedSelect(), ['id' => 'after', 'type' => 'TextInput']];

        self::assertSame($nested, ConditionalStructure::nest(ConditionalStructure::flatten($nested)));
    }

    public function testAnEmptyRuleSurvivesTheRoundTrip(): void
    {
        $select = self::nestedSelect();
        $select['conditions'] = [['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1', 'children' => []]];

        self::assertSame([$select], ConditionalStructure::nest(ConditionalStructure::flatten([$select])));
    }

    public function testConditionsInsideContainersAndRulesAreFlattenedRecursively(): void
    {
        $inner = ['id' => 'inner', 'type' => 'Select', 'options' => [['id' => 'i1', 'label' => 'X']], 'conditions' => [
            ['id' => 'ri', 'operator' => 'is', 'optionId' => 'i1', 'children' => [['id' => 'deep', 'type' => 'TextInput']]],
        ]];
        $outer = ['id' => 'outer', 'type' => 'Select', 'options' => [['id' => 'o1', 'label' => 'A']], 'conditions' => [
            ['id' => 'ro', 'operator' => 'is', 'optionId' => 'o1', 'children' => [$inner]],
        ]];
        $nested = [['id' => 'fs', 'type' => 'FieldSet', 'children' => [$outer]]];

        $flat = ConditionalStructure::flatten($nested);

        $children = Options::at($flat[0], 'children');
        self::assertSame(['outer', 'inner', 'deep'], array_column($children, 'id'));
        self::assertSame(['owner' => 'outer', 'rule' => 'ro'], Options::at($children, 1)['condition']);
        self::assertSame(['owner' => 'inner', 'rule' => 'ri'], Options::at($children, 2)['condition']);
        self::assertSame($nested, ConditionalStructure::nest($flat));
    }

    public function testBlocksWhoseOwnerIsMissingAreKeptAsPlainBlocks(): void
    {
        $nested = ConditionalStructure::nest([
            ['id' => 'lonely', 'type' => 'TextInput', 'condition' => ['owner' => 'gone', 'rule' => 'r']],
        ]);

        self::assertSame([['id' => 'lonely', 'type' => 'TextInput']], $nested);
    }

    public function testBlocksWithoutConditionsAreLeftUntouched(): void
    {
        $blocks = [['id' => 'a', 'type' => 'TextInput'], ['id' => 'b', 'type' => 'Select', 'conditions' => []]];

        self::assertSame($blocks, ConditionalStructure::flatten($blocks));
        self::assertSame($blocks, ConditionalStructure::nest($blocks));
    }

    /**
     * @param mixed $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('activityProvider')]
    public function testIsActiveEvaluatesTheRuleAgainstTheOwnerValue(string $rule, mixed $value, bool $expected): void
    {
        $owner = ConditionalStructure::flatten([self::nestedSelect()])[0];

        self::assertSame($expected, ConditionalStructure::isActive($owner, $rule, $value));
    }

    /**
     * @return iterable<string, array{string, mixed, bool}>
     */
    public static function activityProvider(): iterable
    {
        yield 'is: option chosen' => ['r1', 'Oui', true];
        yield 'is: other option' => ['r1', 'Non', false];
        yield 'is: nothing chosen' => ['r1', null, false];
        yield 'is: multiple, contains' => ['r1', ['Oui', 'Non'], true];
        yield 'is: multiple, does not contain' => ['r1', ['Non'], false];
        yield 'is_not: option chosen' => ['r2', 'Non', false];
        yield 'is_not: other option' => ['r2', 'Oui', true];
        yield 'is_not: nothing chosen' => ['r2', null, true];
        yield 'unknown rule' => ['nope', 'Oui', false];
    }

    public function testRuleReturnsNullWhenTheOptionWasRemoved(): void
    {
        $owner = ConditionalStructure::flatten([self::nestedSelect()])[0];
        $owner['options'] = [['id' => 'o2', 'label' => 'Non']];

        self::assertNull(ConditionalStructure::rule($owner, 'r1'));
        self::assertSame(['operator' => 'is_not', 'optionLabel' => 'Non'], ConditionalStructure::rule($owner, 'r2'));
    }

    /**
     * @return array<string, mixed>
     */
    private static function nestedSelect(): array
    {
        return [
            'id' => 'sel',
            'type' => 'Select',
            'label' => 'Choix',
            'options' => [['id' => 'o1', 'label' => 'Oui'], ['id' => 'o2', 'label' => 'Non']],
            'conditions' => [
                ['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1', 'children' => [['id' => 'child-a', 'type' => 'TextInput', 'label' => 'A']]],
                ['id' => 'r2', 'operator' => 'is_not', 'optionId' => 'o2', 'children' => [['id' => 'child-b', 'type' => 'TextInput', 'label' => 'B']]],
            ],
        ];
    }
}
