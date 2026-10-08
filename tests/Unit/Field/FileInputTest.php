<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\FileInput;
use AppoloDev\FormBuilderBundle\FormType\FileRepeatableType;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
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
        $formBuilder->expects(self::once())->method('add')->with('file-1', FileRepeatableType::class, self::callback(static function (array $options) use ($expectedMimeTypes): bool {
            $constraints = Options::at($options, 'entry_options', 'file_options', 'constraints');
            if ([] === $expectedMimeTypes) {
                self::assertSame([], $constraints);

                return true;
            }
            self::assertCount(1, $constraints);
            self::assertInstanceOf(File::class, $constraints[0]);
            self::assertSame($expectedMimeTypes, $constraints[0]->mimeTypes);

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
        $formBuilder->expects(self::once())->method('add')->with('file-1', FileRepeatableType::class, self::callback(static function (array $options): bool {
            self::assertSame(2, Options::at($options, 'attr')['maxItems']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('multiplicityProvider')]
    public function testAllowMultipleControlsTheMaximumNumberOfFiles(array $config, int $expectedMaxItems): void
    {
        $field = new FileInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'file-1', ...$config]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('file-1', FileRepeatableType::class, self::callback(static function (array $options) use ($expectedMaxItems): bool {
            self::assertSame($expectedMaxItems, Options::at($options, 'attr')['maxItems']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, int}>
     */
    public static function multiplicityProvider(): iterable
    {
        yield 'single file' => [['allowMultiple' => false], 1];
        yield 'single file wins over maxItems' => [['allowMultiple' => false, 'maxItems' => 4], 1];
        yield 'multiple, default maximum' => [['allowMultiple' => true], 5];
        yield 'multiple, configured maximum' => [['allowMultiple' => true, 'maxItems' => 3], 3];
        yield 'legacy maxItems only' => [['maxItems' => 2], 2];
        yield 'nothing configured' => [[], 5];
    }
}
