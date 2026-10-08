<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Service;

use AppoloDev\FormBuilderBundle\Service\AnswerFiles;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormAnswer;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayout;
use PHPUnit\Framework\TestCase;

class AnswerFilesTest extends TestCase
{
    public function testItFindsFilesAtTheTopLevelInFieldSetsRepeatablesAndConditionalBlocks(): void
    {
        $answer = new TestFormAnswer();
        $answer->setFormLayout((new TestFormLayout())->setStructure([
            ['id' => 'top', 'type' => 'FileInput'],
            ['id' => 'fs', 'type' => 'FieldSet', 'children' => [['id' => 'inFs', 'type' => 'FileInput']]],
            ['id' => 'rep', 'type' => 'Repeatable', 'children' => [['id' => 'inRep', 'type' => 'FileInput']]],
            [
                'id' => 'sel',
                'type' => 'Select',
                'options' => [['id' => 'o1', 'label' => 'Oui']],
                'conditions' => [['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1', 'children' => [['id' => 'cond', 'type' => 'FileInput']]]],
            ],
            ['id' => 'text', 'type' => 'TextInput'],
        ]));
        $answer->setAnswerData([
            'top' => [['file' => ['filename' => 'a.pdf']]],
            'fs' => ['inFs' => [['file' => ['filename' => 'b.pdf']]]],
            'rep' => [['inRep' => [['file' => ['filename' => 'c.pdf']]]], ['inRep' => [['file' => ['filename' => 'd.pdf']]]]],
            'sel' => 'Oui',
            'cond' => [['file' => ['filename' => 'e.pdf']]],
            'text' => 'x',
        ]);

        $files = new AnswerFiles();

        self::assertSame(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf', 'e.pdf'], $files->filenames($answer));
        self::assertTrue($files->owns($answer, 'c.pdf'));
        self::assertFalse($files->owns($answer, 'other.pdf'));
        self::assertFalse($files->owns($answer, '../a.pdf'));
    }
}
