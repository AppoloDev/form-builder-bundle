<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Twig;

use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormImageExtension extends AbstractExtension
{
    public function __construct(private readonly FormFileUploader $fileUploader)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('convertToBase64', function (string $fileName): ?string {
                return $this->convertToBase64($fileName);
            }),
            new TwigFunction('formImageFileUri', function (string $fileName): ?string {
                return $this->formImageFileUri($fileName);
            }),
        ];
    }

    public function convertToBase64(string $fileName): ?string
    {
        $file = $this->fileUploader->getFile($fileName);
        if (is_null($file)) {
            return null;
        }
        $content = file_get_contents($file->getPathname());

        if (!is_string($content)) {
            return null;
        }

        $mimeType = $file->getMimeType() ?? 'image/jpeg';

        return 'data:'.$mimeType.';base64,'.base64_encode($content);
    }

    /**
     * Returns a file:// URI to the image on local disk, for use by wkhtmltopdf
     * (which fails to reliably render large base64 data URIs), instead of
     * embedding the image as base64. Requires the "enable-local-file-access"
     * Snappy option.
     */
    public function formImageFileUri(string $fileName): ?string
    {
        $file = $this->fileUploader->getFile($fileName);
        if (is_null($file)) {
            return null;
        }

        return 'file://'.$file->getPathname();
    }
}
