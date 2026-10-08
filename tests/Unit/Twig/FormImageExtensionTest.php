<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Twig;

use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use AppoloDev\FormBuilderBundle\Twig\FormImageExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FormImageExtensionTest extends TestCase
{
    private string $uploadPath;
    private FormFileUploader $uploader;

    protected function setUp(): void
    {
        $this->uploadPath = sys_get_temp_dir().'/form_image_extension_test_'.uniqid().'/';
        $this->uploader = new FormFileUploader($this->uploadPath);
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->uploadPath)) {
            $filesystem->remove($this->uploadPath);
        }
    }

    public function testConvertToBase64ReturnsADataUri(): void
    {
        $fileName = $this->writeFile('contenu-image');

        $result = (new FormImageExtension($this->uploader))->convertToBase64($fileName);

        self::assertNotNull($result);
        self::assertStringStartsWith('data:', $result);
        self::assertStringContainsString(base64_encode('contenu-image'), $result);
    }

    public function testConvertToBase64ReturnsNullWhenFileIsMissing(): void
    {
        self::assertNull((new FormImageExtension($this->uploader))->convertToBase64('unknown.jpg'));
    }

    public function testFormImageFileUriReturnsAFileUri(): void
    {
        $fileName = $this->writeFile('contenu-image');

        $result = (new FormImageExtension($this->uploader))->formImageFileUri($fileName);

        self::assertNotNull($result);
        self::assertStringStartsWith('file://', $result);
        self::assertStringContainsString($fileName, $result);
    }

    public function testFormImageFileUriReturnsNullWhenFileIsMissing(): void
    {
        self::assertNull((new FormImageExtension($this->uploader))->formImageFileUri('unknown.jpg'));
    }

    private function writeFile(string $content): string
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->uploadPath);

        $fileName = uniqid().'.jpg';
        file_put_contents($this->uploadPath.$fileName, $content);

        return $fileName;
    }
}
