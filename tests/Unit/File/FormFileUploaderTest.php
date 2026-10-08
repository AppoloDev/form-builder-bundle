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

    private function buildUploadedFile(string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'form_file_uploader_source_');
        self::assertNotFalse($path);
        file_put_contents($path, $content);

        return new UploadedFile($path, 'original.txt', null, null, true);
    }
}
