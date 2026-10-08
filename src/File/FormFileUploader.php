<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\File;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FormFileUploader
{
    private readonly Filesystem $filesystem;

    public function __construct(
        #[Autowire('%form_builder.upload_path%')]
        private readonly string $formFileUploadPath,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @return array<string, string>
     */
    public function upload(UploadedFile $file): array
    {
        $this->checkUploadDirectory();

        $fileName = uniqid().'.'.$file->getClientOriginalExtension();
        $this->copy($file, $fileName);

        return [
            'filename' => $fileName,
            'originalFilename' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
        ];
    }

    /**
     * @param array<mixed, mixed> $fileData
     */
    public function hydrate(array $fileData): ?UploadedFile
    {
        $filename = $fileData['filename'] ?? null;
        $originalFilename = $fileData['originalFilename'] ?? null;

        if (
            is_string($filename)
            && $this->filesystem->exists($this->formFileUploadPath.$filename)
            && is_string($originalFilename)
        ) {
            return new UploadedFile($this->formFileUploadPath.$filename, $originalFilename);
        }

        return null;
    }

    /**
     * @param array<mixed, mixed>|null $fileData
     */
    public function removeFile(?array $fileData): void
    {
        $filename = $fileData['filename'] ?? null;

        if (is_string($filename) && $this->filesystem->exists($this->formFileUploadPath.$filename)) {
            $this->filesystem->remove($this->formFileUploadPath.$filename);
        }
    }

    public function getFile(string $filePath): ?File
    {
        $target = $this->formFileUploadPath.$filePath;

        if (!$this->filesystem->exists($target)) {
            return null;
        }

        return new File($target);
    }

    private function checkUploadDirectory(): void
    {
        if ($this->filesystem->exists($this->formFileUploadPath)) {
            return;
        }

        try {
            $this->filesystem->mkdir($this->formFileUploadPath);
        } catch (IOExceptionInterface $exception) {
            throw new FileException(sprintf('Unable to create the "%s" directory.', $this->formFileUploadPath), 0, $exception);
        }
    }

    private function copy(UploadedFile $file, string $fileName): void
    {
        $target = $this->formFileUploadPath.$fileName;
        $this->filesystem->copy($file->getPathname(), $target, overwriteNewerFiles: true);
        $this->filesystem->chmod($target, 0666, umask());
    }
}
