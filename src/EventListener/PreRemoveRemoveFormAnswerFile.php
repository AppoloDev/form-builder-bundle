<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\EventListener;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use AppoloDev\FormBuilderBundle\Service\FieldKindResolver;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Supprime du disque les fichiers d'une réponse quand elle est supprimée. Les champs fichier sont
 * repérés d'après la structure du modèle (type `FileInput`), y compris dans un `FieldSet` ou un
 * `Repeatable`, et non d'après le format des ids de blocs.
 */
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
        if (!$object instanceof FormAnswerInterface) {
            return;
        }

        $this->removeFilesOfBlocks(
            FormLayoutBlock::listFromArray($object->getFormLayout()->getStructure()),
            $object->getAnswerData(),
        );
    }

    /**
     * @param FormLayoutBlock[]       $blocks
     * @param array<array-key, mixed> $data   valeurs des blocs, indexées par id de bloc
     */
    private function removeFilesOfBlocks(array $blocks, array $data): void
    {
        foreach ($blocks as $block) {
            $value = null === $block->id ? null : ($data[$block->id] ?? null);
            if (!\is_array($value)) {
                continue;
            }

            if ('FileInput' === $block->type) {
                $this->removeFiles($value);
                continue;
            }

            match (FieldKindResolver::resolve((string) $block->type)) {
                FieldKind::Container => $this->removeFilesOfBlocks($block->children, $value),
                FieldKind::Repeatable => $this->removeFilesOfRows($block->children, $value),
                default => null,
            };
        }
    }

    /**
     * @param FormLayoutBlock[]       $children
     * @param array<array-key, mixed> $rows
     */
    private function removeFilesOfRows(array $children, array $rows): void
    {
        foreach ($rows as $row) {
            if (\is_array($row)) {
                $this->removeFilesOfBlocks($children, $row);
            }
        }
    }

    /**
     * @param array<array-key, mixed> $files
     */
    private function removeFiles(array $files): void
    {
        foreach ($files as $file) {
            if (\is_array($file) && isset($file['file']) && \is_array($file['file'])) {
                $this->formFileUploader->removeFile($file['file']);
            }
        }
    }
}
