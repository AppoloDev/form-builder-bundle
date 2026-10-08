<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\EventListener;

use AppoloDev\FormBuilderBundle\EventListener\PreRemoveRemoveFormAnswerFile;
use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormAnswer;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayout;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class PreRemoveRemoveFormAnswerFileTest extends TestCase
{
    public function testRemovesFilesOfATopLevelFileInput(): void
    {
        $removed = $this->removedFilesFor(
            [['id' => 'FileInput-1', 'type' => 'FileInput', 'label' => 'Docs']],
            ['FileInput-1' => [['file' => ['filename' => 'a.pdf']]]],
        );

        self::assertSame([['filename' => 'a.pdf']], $removed);
    }

    public function testRemovesFilesNestedInAFieldSet(): void
    {
        $removed = $this->removedFilesFor(
            [['id' => 'FieldSet-1', 'type' => 'FieldSet', 'children' => [
                ['id' => 'FileInput-1', 'type' => 'FileInput', 'label' => 'Docs'],
            ]]],
            ['FieldSet-1' => ['FileInput-1' => [['file' => ['filename' => 'nested.pdf']]]]],
        );

        self::assertSame([['filename' => 'nested.pdf']], $removed);
    }

    public function testRemovesFilesOfEachRepeatableRow(): void
    {
        $removed = $this->removedFilesFor(
            [['id' => 'Repeatable-1', 'type' => 'Repeatable', 'children' => [
                ['id' => 'FileInput-1', 'type' => 'FileInput', 'label' => 'Docs'],
            ]]],
            ['Repeatable-1' => [
                ['FileInput-1' => [['file' => ['filename' => 'row1.pdf']]]],
                ['FileInput-1' => [['file' => ['filename' => 'row2.pdf']]]],
            ]],
        );

        self::assertSame([['filename' => 'row1.pdf'], ['filename' => 'row2.pdf']], $removed);
    }

    public function testRemovesFilesOfAConditionalFileInput(): void
    {
        $removed = $this->removedFilesFor(
            [[
                'id' => 'Select-1',
                'type' => 'Select',
                'options' => [['id' => 'o1', 'label' => 'Oui']],
                'conditions' => [['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1', 'children' => [
                    ['id' => 'FileInput-1', 'type' => 'FileInput', 'label' => 'Justificatif'],
                ]]],
            ]],
            ['Select-1' => 'Oui', 'FileInput-1' => [['file' => ['filename' => 'cond.pdf']]]],
        );

        self::assertSame([['filename' => 'cond.pdf']], $removed);
    }

    public function testIgnoresFieldsThatAreNotFileInputs(): void
    {
        $removed = $this->removedFilesFor(
            [['id' => 'TextInput-1', 'type' => 'TextInput', 'label' => 'Nom']],
            ['TextInput-1' => 'une valeur simple'],
        );

        self::assertSame([], $removed);
    }

    public function testIgnoresNonFormAnswerEntities(): void
    {
        $uploader = $this->createMock(FormFileUploader::class);
        $uploader->expects(self::never())->method('removeFile');

        $args = self::createStub(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn(new \stdClass());

        (new PreRemoveRemoveFormAnswerFile($uploader))->preRemove($args);
    }

    /**
     * @param array<int, array<string, mixed>> $structure
     * @param array<string, mixed>             $answerData
     *
     * @return list<array<mixed>|null>
     */
    private function removedFilesFor(array $structure, array $answerData): array
    {
        $answer = new TestFormAnswer();
        $answer->setFormLayout((new TestFormLayout())->setStructure($structure));
        $answer->setAnswerData($answerData);

        $removed = [];
        $uploader = self::createStub(FormFileUploader::class);
        $uploader->method('removeFile')->willReturnCallback(static function (?array $file) use (&$removed): void {
            $removed[] = $file;
        });

        $args = self::createStub(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($answer);

        (new PreRemoveRemoveFormAnswerFile($uploader))->preRemove($args);

        return $removed;
    }
}
