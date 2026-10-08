<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\FormType;

use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileRepeatableType extends AbstractType
{
    /** @var array<int|string, mixed> */
    private array $filesData = [];

    public function __construct(private readonly FormFileUploader $formFileUploader)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent): void {
            $this->filesData = \is_array($formEvent->getData()) ? $formEvent->getData() : [];

            /**
             * @var string $k
             * @var Form   $child
             */
            foreach ($formEvent->getForm() as $k => $child) {
                $fileData = $this->filesData[$k] ?? null;
                if (\is_array($fileData) && isset($fileData['file']) && \is_array($fileData['file'])) {
                    $child->get('file')->setData($this->formFileUploader->hydrate($fileData['file']));
                } else {
                    $child->get('file')->setData(null);
                }
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $formEvent): void {
            $files = \is_array($formEvent->getData()) ? $formEvent->getData() : [];

            foreach ($files as $k => &$childData) {
                if (!\is_array($childData)) {
                    continue;
                }

                if (\array_key_exists('file', $childData)) {
                    if (null === $childData['file']) {
                        $existingFileData = $this->filesData[$k] ?? null;
                        if (\is_array($existingFileData) && isset($existingFileData['file'])) {
                            $childData['file'] = $existingFileData['file'];
                        }
                    } elseif ($childData['file'] instanceof UploadedFile) {
                        $childData['file'] = $this->formFileUploader->upload($childData['file']);
                    }
                }
            }

            $filesToKeep = array_filter($files, static fn ($item): bool => \is_array($item) && true !== ($item['delete'] ?? false));
            $filesToDelete = array_filter($files, static fn ($item): bool => \is_array($item) && true === ($item['delete'] ?? false));

            foreach ($filesToDelete as $fileToDelete) {
                if (\is_array($fileToDelete) && \array_key_exists('file', $fileToDelete)) {
                    $fileData = \is_array($fileToDelete['file']) ? $fileToDelete['file'] : null;
                    $this->formFileUploader->removeFile($fileData);
                }
            }

            $formEvent->setData($filesToKeep);
        });
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
