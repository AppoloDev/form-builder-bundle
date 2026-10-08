<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Entity;

use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormAnswer;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayout;
use PHPUnit\Framework\TestCase;

class AbstractFormAnswerTest extends TestCase
{
    public function testSetAnswerDataThenGetAnswerDataRoundTripsSimpleValues(): void
    {
        $answer = $this->answerFor([
            ['id' => 'name', 'type' => 'TextInput', 'label' => 'Nom'],
            ['id' => 'age', 'type' => 'NumberInput', 'label' => 'Âge'],
        ]);

        $answer->setAnswerData(['name' => 'Alice', 'age' => 30]);

        self::assertSame(['name' => 'Alice', 'age' => 30], $answer->getAnswerData());
        self::assertCount(2, $answer->getFieldValues());
    }

    public function testSetAnswerDataIgnoresKeysThatMatchNoField(): void
    {
        $answer = $this->answerFor([['id' => 'name', 'type' => 'TextInput', 'label' => 'Nom']]);

        $answer->setAnswerData(['name' => 'Alice', 'unknown' => 'x', 'internalReference' => 'REF-1']);

        self::assertSame(['name' => 'Alice'], $answer->getAnswerData());
    }

    public function testSetAnswerDataReplacesPreviousValues(): void
    {
        $answer = $this->answerFor([['id' => 'name', 'type' => 'TextInput', 'label' => 'Nom']]);

        $answer->setAnswerData(['name' => 'Alice']);
        $answer->setAnswerData(['name' => 'Bob']);

        self::assertSame(['name' => 'Bob'], $answer->getAnswerData());
        self::assertCount(1, $answer->getFieldValues());
    }

    public function testRepeatableValuesAreStoredPerRowIndexAndRebuiltInOrder(): void
    {
        $answer = $this->answerFor([
            ['id' => 'rows', 'type' => 'Repeatable', 'children' => [
                ['id' => 'item', 'type' => 'TextInput', 'label' => 'Item'],
            ]],
        ]);

        $answer->setAnswerData(['rows' => [['item' => 'A'], ['item' => 'B']]]);

        self::assertSame(['rows' => [['item' => 'A'], ['item' => 'B']]], $answer->getAnswerData());
        self::assertCount(2, $answer->getFieldValues());
    }

    public function testRepeatableChildKeysUnknownToTheLayoutAreSkipped(): void
    {
        $answer = $this->answerFor([
            ['id' => 'rows', 'type' => 'Repeatable', 'children' => [
                ['id' => 'item', 'type' => 'TextInput', 'label' => 'Item'],
            ]],
        ]);

        $answer->setAnswerData(['rows' => [['item' => 'A', 'ghost' => 'x'], 'not-an-array']]);

        self::assertSame(['rows' => [['item' => 'A']]], $answer->getAnswerData());
    }

    public function testAddFieldValueLinksTheValueBackToTheAnswerOnlyOnce(): void
    {
        $answer = $this->answerFor([['id' => 'name', 'type' => 'TextInput', 'label' => 'Nom']]);
        $answer->setAnswerData(['name' => 'Alice']);
        $value = $answer->getFieldValues()->first();
        self::assertNotFalse($value);

        $answer->addFieldValue($value);

        self::assertCount(1, $answer->getFieldValues());
        self::assertSame($answer, $value->getFormAnswer());

        $answer->removeFieldValue($value);
        self::assertCount(0, $answer->getFieldValues());
    }

    /**
     * @param array<int, array<string, mixed>> $structure
     */
    private function answerFor(array $structure): TestFormAnswer
    {
        $answer = new TestFormAnswer();
        $answer->setFormLayout((new TestFormLayout())->setStructure($structure));

        return $answer;
    }
}
