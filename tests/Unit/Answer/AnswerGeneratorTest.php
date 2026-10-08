<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Answer;

use AppoloDev\FormBuilderBundle\Answer\AnswerGenerator;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use PHPUnit\Framework\TestCase;

class AnswerGeneratorTest extends TestCase
{
    private AnswerGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new AnswerGenerator();
    }

    public function testGenerateSkipsDisplayOnlyFieldsAssignsLeafValuesAndRecursesIntoFieldSets(): void
    {
        $structure = [
            ['id' => 'title1', 'type' => 'Title', 'label' => 'Section'],
            ['id' => 'leaf1', 'type' => 'TextInput', 'label' => 'Nom'],
            ['id' => 'fieldset1', 'type' => 'FieldSet', 'label' => 'Groupe', 'children' => [
                ['id' => 'leaf2', 'type' => 'TextInput', 'label' => 'Ville'],
            ]],
        ];
        $answerData = [
            'leaf1' => 'Alice',
            'fieldset1' => ['leaf2' => 'Paris'],
        ];

        $result = $this->generator->generate($structure, $answerData);

        self::assertArrayNotHasKey('value', $result[0]);
        self::assertSame('Alice', $result[1]['value']);
        self::assertArrayNotHasKey('value', $result[2]);
        self::assertSame('Paris', Options::at($result[2], 'children', 0)['value']);
    }

    public function testGenerateBuildsRepeatableRowValues(): void
    {
        $structure = [
            ['id' => 'rep1', 'type' => 'Repeatable', 'label' => 'Lignes', 'children' => [
                ['id' => 'child1', 'type' => 'TextInput', 'label' => 'Item'],
            ]],
        ];
        $answerData = [
            'rep1' => [
                ['child1' => 'A'],
                ['child1' => 'B'],
            ],
        ];

        $result = $this->generator->generate($structure, $answerData);

        self::assertSame('A', Options::at($result[0], 'value', 0, 0)['value']);
        self::assertSame('B', Options::at($result[0], 'value', 1, 0)['value']);
    }

    public function testFlattenToFieldsSkipsDisplayOnlyAndRecursesIntoFieldSets(): void
    {
        $answers = [
            ['id' => 'title1', 'type' => 'Title', 'label' => 'Section'],
            ['id' => 'leaf1', 'type' => 'TextInput', 'label' => 'Nom', 'value' => 'Alice'],
            ['id' => 'fieldset1', 'type' => 'FieldSet', 'label' => 'Groupe', 'children' => [
                ['id' => 'leaf2', 'type' => 'TextInput', 'label' => 'Ville', 'value' => 'Paris'],
            ]],
        ];

        $fields = $this->generator->flattenToFields($answers);

        self::assertSame([
            ['label' => 'Nom', 'value' => 'Alice'],
            ['label' => 'Ville', 'value' => 'Paris'],
        ], $fields);
    }

    public function testFlattenToFieldsJoinsRepeatableRowValues(): void
    {
        $answers = [
            [
                'id' => 'rep1',
                'type' => 'Repeatable',
                'label' => 'Lignes',
                'value' => [
                    [['id' => 'child1', 'type' => 'TextInput', 'label' => 'Item', 'value' => 'A']],
                    [['id' => 'child1', 'type' => 'TextInput', 'label' => 'Item', 'value' => 'B']],
                ],
            ],
        ];

        $fields = $this->generator->flattenToFields($answers);

        self::assertSame([
            ['label' => 'Item', 'value' => 'A - B'],
        ], $fields);
    }

    public function testGenerateKeepsConditionalBlocksOnlyWhenTheirRuleIsSatisfied(): void
    {
        $structure = [self::selectWithConditions(), ['id' => 'after', 'type' => 'TextInput', 'label' => 'Après']];

        $active = $this->generator->generate($structure, ['sel' => 'Oui', 'detail' => 'précision', 'after' => 'x']);
        $inactive = $this->generator->generate($structure, ['sel' => 'Non', 'detail' => 'ignoré', 'after' => 'x']);

        self::assertSame(['sel', 'detail', 'after'], array_column($active, 'id'));
        self::assertSame('précision', $active[1]['value']);
        self::assertSame(['sel', 'after'], array_column($inactive, 'id'));
    }

    public function testGenerateEvaluatesConditionsRowByRowInsideARepeatable(): void
    {
        $structure = [[
            'id' => 'rep',
            'type' => 'Repeatable',
            'children' => [self::selectWithConditions()],
        ]];

        $result = $this->generator->generate($structure, ['rep' => [
            ['sel' => 'Oui', 'detail' => 'A'],
            ['sel' => 'Non', 'detail' => 'B'],
        ]]);

        $rows = Options::at($result[0], 'value');
        self::assertSame(['sel', 'detail'], array_column(Options::at($rows, 0), 'id'));
        self::assertSame(['sel'], array_column(Options::at($rows, 1), 'id'));
    }

    /**
     * @return array<string, mixed>
     */
    private static function selectWithConditions(): array
    {
        return [
            'id' => 'sel',
            'type' => 'Select',
            'label' => 'Choix',
            'options' => [['id' => 'o1', 'label' => 'Oui'], ['id' => 'o2', 'label' => 'Non']],
            'conditions' => [
                ['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1', 'children' => [['id' => 'detail', 'type' => 'TextInput', 'label' => 'Détail']]],
            ],
        ];
    }
}
