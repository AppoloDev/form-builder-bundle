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
use Symfony\Component\Validator\Validation;

class FileInputTest extends TestCase
{
    /**
     * Fichier réel validé par la contrainte File du champ : extension et contenu (type MIME détecté).
     */
    #[DataProvider('acceptedFileProvider')]
    public function testValidatesFileExtensionAndContent(string $acceptedFile, string $filename, string $content, bool $valid): void
    {
        $path = sys_get_temp_dir().'/fb-'.bin2hex(random_bytes(4)).'-'.$filename;
        file_put_contents($path, $content);

        try {
            $violations = Validation::createValidator()->validate($path, self::fileConstraint($acceptedFile));
            self::assertSame($valid, 0 === \count($violations), (string) $violations);
        } finally {
            unlink($path);
        }
    }

    /**
     * @return iterable<string, array{string, string, string, bool}>
     */
    public static function acceptedFileProvider(): iterable
    {
        $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', true);
        $pdf = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n";

        yield 'image: png' => ['image', 'photo.png', $png, true];
        yield 'image: pdf refused' => ['image', 'devis.pdf', $pdf, false];
        yield 'document: png' => ['document', 'photo.png', $png, true];
        yield 'document: pdf' => ['document', 'devis.pdf', $pdf, true];
        yield 'document: csv detected as text/plain' => ['document', 'export.csv', "a;b\n1;2\n", true];
        yield 'document: txt' => ['document', 'note.txt', 'Bonjour', true];
        yield 'document: text renamed .pdf refused' => ['document', 'faux.pdf', 'Bonjour', false];
        yield 'document: unknown extension refused' => ['document', 'script.php', '<?php echo 1;', false];
        yield 'document: svg refused' => ['document', 'logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>', false];
        // Anciennes valeurs (PDF seul, images et PDF) et valeur absente : documents.
        yield 'legacy pdf: docs accepted' => ['file', 'note.txt', 'Bonjour', true];
        yield 'legacy both' => ['both', 'devis.pdf', $pdf, true];
        yield 'missing' => ['', 'export.csv', "a;b\n", true];
    }

    private static function fileConstraint(string $acceptedFile): File
    {
        $field = new class extends FileInput {
            /** @return array<string, mixed> */
            public function options(): array
            {
                return $this->getFieldOptions();
            }
        };
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'file-1', 'acceptedFile' => $acceptedFile]), []);
        $constraints = Options::at($field->options(), 'entry_options', 'file_options', 'constraints');
        self::assertIsArray($constraints);
        self::assertCount(1, $constraints);
        self::assertInstanceOf(File::class, $constraints[0]);

        return $constraints[0];
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
        yield 'configured maximum' => [['maxItems' => 3], 3];
        yield 'builder string' => [['maxItems' => '3'], 3];
        yield 'empty means unlimited' => [['maxItems' => ''], 0];
        yield 'zero means unlimited' => [['maxItems' => 0], 0];
        yield 'maxItems wins over legacy allowMultiple' => [['allowMultiple' => false, 'maxItems' => 4], 4];
        yield 'legacy single file' => [['allowMultiple' => false], 1];
        yield 'legacy multiple' => [['allowMultiple' => true], 5];
        yield 'nothing configured' => [[], 5];
    }
}
