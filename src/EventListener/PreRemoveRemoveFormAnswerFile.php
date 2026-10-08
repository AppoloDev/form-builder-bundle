<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\EventListener;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::preRemove)]
class PreRemoveRemoveFormAnswerFile
{
    public function __construct(private readonly FormFileUploader $formFileUploader)
    {
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof FormAnswerInterface) {
            $this->parseContent($object->getAnswerData());
        }
    }

    /**
     * @param array<array-key, mixed> $content
     */
    protected function parseContent(array $content): void
    {
        foreach ($content as $fieldId => $fieldValue) {
            if (!\is_array($fieldValue)) {
                continue;
            }

            if (str_starts_with($fieldId, 'file_')) {
                $this->removeFiles($fieldValue);
            } elseif (str_starts_with($fieldId, 'fieldset_')) {
                $this->parseContent($fieldValue);
            } elseif (str_starts_with($fieldId, 'repeatable_')) {
                foreach ($fieldValue as $repeatableValue) {
                    if (\is_array($repeatableValue)) {
                        $this->parseContent($repeatableValue);
                    }
                }
            }
        }
    }

    /**
     * @param array<array-key, mixed> $files
     */
    protected function removeFiles(array $files): void
    {
        foreach ($files as $file) {
            if (\is_array($file) && isset($file['file']) && \is_array($file['file'])) {
                $this->formFileUploader->removeFile($file['file']);
            }
        }
    }
}
