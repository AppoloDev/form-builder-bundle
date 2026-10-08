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

    private readonly string $formFileUploadPath;

    public function __construct(
        #[Autowire('%form_builder.upload_path%')]
        string $formFileUploadPath,
    ) {
        $this->formFileUploadPath = rtrim($formFileUploadPath, '/').'/';
        $this->filesystem = new Filesystem();
    }

    /**
     * @return array<string, string>
     */
    public function upload(UploadedFile $file): array
    {
        $this->checkUploadDirectory();

        // Nom imprévisible et extension déduite du contenu : ni le nom ni l'extension du client ne sont de confiance.
        $extension = $this->safeExtension($file);
        $fileName = bin2hex(random_bytes(16)).'.'.$extension;
        $this->copy($file, $fileName);

        return [
            'filename' => $fileName,
            'originalFilename' => $file->getClientOriginalName(),
            'extension' => $extension,
        ];
    }

    /**
     * @param array<mixed, mixed> $fileData
     */
    public function hydrate(array $fileData): ?UploadedFile
    {
        $filename = $fileData['filename'] ?? null;
        $originalFilename = $fileData['originalFilename'] ?? null;

        $path = \is_string($filename) ? $this->pathOf($filename) : null;

        if (null !== $path && $this->filesystem->exists($path) && \is_string($originalFilename)) {
            return new UploadedFile($path, $originalFilename);
        }

        return null;
    }

    /**
     * @param array<mixed, mixed>|null $fileData
     */
    public function removeFile(?array $fileData): void
    {
        $filename = $fileData['filename'] ?? null;
        $path = \is_string($filename) ? $this->pathOf($filename) : null;

        if (null !== $path && $this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    public function getFile(string $filePath): ?File
    {
        $target = $this->pathOf($filePath);

        if (null === $target || !$this->filesystem->exists($target)) {
            return null;
        }

        return new File($target);
    }

    /**
     * Chemin d'un fichier du dossier d'envoi, ou null si le nom n'est pas un simple nom de fichier
     * (séparateur de répertoire, `..`, octet nul) : un nom venant d'une URL ou d'une requête ne doit
     * jamais permettre de sortir du dossier.
     */
    private function pathOf(string $filename): ?string
    {
        if ('' === $filename || '.' === $filename || '..' === $filename || basename($filename) !== $filename || str_contains($filename, "\0")) {
            return null;
        }

        return $this->formFileUploadPath.$filename;
    }

    private function safeExtension(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? strtolower($file->getClientOriginalExtension());
        $extension = preg_replace('/[^a-z0-9]/', '', strtolower($extension)) ?? '';

        return '' === $extension ? 'bin' : substr($extension, 0, 10);
    }

    private function checkUploadDirectory(): void
    {
        if ($this->filesystem->exists($this->formFileUploadPath)) {
            return;
        }

        try {
            $this->filesystem->mkdir($this->formFileUploadPath);
        } catch (IOExceptionInterface $exception) {
            throw new FileException(\sprintf('Unable to create the "%s" directory.', $this->formFileUploadPath), 0, $exception);
        }
    }

    private function copy(UploadedFile $file, string $fileName): void
    {
        $target = $this->formFileUploadPath.$fileName;
        $this->filesystem->copy($file->getPathname(), $target, overwriteNewerFiles: true);
        $this->filesystem->chmod($target, 0666, umask());
    }
}
