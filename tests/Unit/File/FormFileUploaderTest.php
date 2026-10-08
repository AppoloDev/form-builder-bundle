<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\File;

use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FormFileUploaderTest extends TestCase
{
    private string $uploadPath;
    private Filesystem $filesystem;
    private FormFileUploader $uploader;

    protected function setUp(): void
    {
        $this->uploadPath = sys_get_temp_dir().'/form_file_uploader_test_'.uniqid().'/';
        $this->filesystem = new Filesystem();
        $this->uploader = new FormFileUploader($this->uploadPath);
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->uploadPath)) {
            $this->filesystem->remove($this->uploadPath);
        }
    }

    public function testUploadCreatesTheDirectoryAndCopiesTheFile(): void
    {
        $uploadedFile = $this->buildUploadedFile('bonjour');

        $result = $this->uploader->upload($uploadedFile);

        self::assertSame('original.txt', $result['originalFilename']);
        self::assertSame('txt', $result['extension']);
        self::assertTrue($this->filesystem->exists($this->uploadPath.$result['filename']));
    }

    public function testHydrateReturnsNullWhenFileIsMissingOnDisk(): void
    {
        self::assertNull($this->uploader->hydrate(['filename' => 'unknown.txt', 'originalFilename' => 'original.txt']));
    }

    public function testHydrateReturnsAnUploadedFileWhenPresentOnDisk(): void
    {
        $uploaded = $this->uploader->upload($this->buildUploadedFile('bonjour'));

        $hydrated = $this->uploader->hydrate($uploaded);

        self::assertInstanceOf(UploadedFile::class, $hydrated);
        self::assertSame($uploaded['originalFilename'], $hydrated->getClientOriginalName());
    }

    public function testRemoveFileDeletesItFromDisk(): void
    {
        $uploaded = $this->uploader->upload($this->buildUploadedFile('bonjour'));
        self::assertTrue($this->filesystem->exists($this->uploadPath.$uploaded['filename']));

        $this->uploader->removeFile($uploaded);

        self::assertFalse($this->filesystem->exists($this->uploadPath.$uploaded['filename']));
    }

    public function testRemoveFileIsANoOpWhenFileDataIsNull(): void
    {
        $this->expectNotToPerformAssertions();

        $this->uploader->removeFile(null);
    }

    public function testGetFileReturnsNullWhenMissing(): void
    {
        self::assertNull($this->uploader->getFile('unknown.txt'));
    }

    public function testGetFileReturnsTheFileWhenPresent(): void
    {
        $uploaded = $this->uploader->upload($this->buildUploadedFile('bonjour'));

        $file = $this->uploader->getFile($uploaded['filename']);

        self::assertNotNull($file);
        self::assertSame('bonjour', file_get_contents($file->getPathname()));
    }

    private function buildUploadedFile(string $content, string $clientName = 'original.txt'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'form_file_uploader_source_');
        self::assertNotFalse($path);
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }

    public function testTheStoredNameIsUnpredictableAndKeepsASafeExtension(): void
    {
        $first = $this->uploader->upload($this->buildUploadedFile('un'));
        $second = $this->uploader->upload($this->buildUploadedFile('deux'));

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}\.[a-z0-9]{1,10}$/', $first['filename']);
        self::assertNotSame($first['filename'], $second['filename']);
    }

    public function testTheExtensionComesFromTheContentNotFromTheClientName(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'form_file_uploader_source_');
        self::assertNotFalse($path);
        file_put_contents($path, "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n1 0 obj\n<< >>\nendobj\ntrailer\n<< >>\n%%EOF");

        $result = $this->uploader->upload(new UploadedFile($path, 'shell.php', null, null, true));

        self::assertSame('pdf', $result['extension']);
        self::assertStringEndsWith('.pdf', $result['filename']);
    }

    public function testAnUnguessableDangerousClientExtensionIsSanitized(): void
    {
        $result = $this->uploader->upload($this->buildUploadedFile('plain text', 'evil.p/h*p'));

        self::assertMatchesRegularExpression('/^[a-z0-9]+$/', $result['extension']);
        self::assertStringNotContainsString('/', $result['filename']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function traversalProvider(): iterable
    {
        yield 'parent directory' => ['../secret.txt'];
        yield 'nested path' => ['sub/dir.txt'];
        yield 'absolute path' => ['/etc/passwd'];
        yield 'windows separator' => ['..\\secret.txt'];
        yield 'dot dot' => ['..'];
        yield 'null byte' => ["a.txt\0.pdf"];
        yield 'empty' => [''];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('traversalProvider')]
    public function testFilenamesThatEscapeTheUploadDirectoryAreRefused(string $filename): void
    {
        // Un fichier réel juste au-dessus du dossier d'envoi ne doit jamais être atteignable.
        $this->filesystem->mkdir($this->uploadPath);
        file_put_contents(\dirname(rtrim($this->uploadPath, '/')).'/secret.txt', 'secret');

        self::assertNull($this->uploader->getFile($filename));
        self::assertNull($this->uploader->hydrate(['filename' => $filename, 'originalFilename' => 'x.txt']));

        $this->uploader->removeFile(['filename' => $filename]);
        self::assertFileExists(\dirname(rtrim($this->uploadPath, '/')).'/secret.txt');
        unlink(\dirname(rtrim($this->uploadPath, '/')).'/secret.txt');
    }

    public function testAMissingTrailingSlashInTheConfiguredPathIsTolerated(): void
    {
        $uploader = new FormFileUploader(rtrim($this->uploadPath, '/'));
        $uploaded = $uploader->upload($this->buildUploadedFile('bonjour'));

        self::assertNotNull($uploader->getFile($uploaded['filename']));
    }
}
