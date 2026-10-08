<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\FileInput;
use AppoloDev\FormBuilderBundle\FormType\FileRepeatableType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class FileInputTest extends TestCase
{
    /**
     * @param string[] $expectedMimeTypes
     */
    #[DataProvider('acceptedFileProvider')]
    public function testMapsAcceptedFileToMimeTypeConstraint(string $acceptedFile, array $expectedMimeTypes): void
    {
        $field = new FileInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'file-1', 'acceptedFile' => $acceptedFile]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('file-1', FileRepeatableType::class, self::callback(function (array $options) use ($expectedMimeTypes): bool {
            $fileOptions = $options['entry_options']['file_options'];
            if ([] === $expectedMimeTypes) {
                self::assertSame([], $fileOptions['constraints']);

                return true;
            }
            self::assertCount(1, $fileOptions['constraints']);
            self::assertInstanceOf(File::class, $fileOptions['constraints'][0]);
            self::assertSame($expectedMimeTypes, $fileOptions['constraints'][0]->mimeTypes);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    /**
     * @return iterable<string, array{string, string[]}>
     */
    public static function acceptedFileProvider(): iterable
    {
        yield 'pdf only' => ['file', ['application/pdf']];
        yield 'image only' => ['image', ['image/*']];
        yield 'both' => ['both', ['application/pdf', 'image/*']];
        yield 'unknown accepts anything' => ['', []];
    }

    public function testUsesConfiguredMaxItemsOrDefaultsToFive(): void
    {
        $field = new FileInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'file-1', 'maxItems' => 2]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('file-1', FileRepeatableType::class, self::callback(function (array $options): bool {
            self::assertSame(2, $options['attr']['maxItems']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
