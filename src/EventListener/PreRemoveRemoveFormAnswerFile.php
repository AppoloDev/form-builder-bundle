<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\EventListener;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\File\FormFileUploader;
use AppoloDev\FormBuilderBundle\Service\AnswerFiles;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Supprime du disque les fichiers d'une réponse quand elle est supprimée.
 */
#[AsDoctrineListener(event: Events::preRemove)]
class PreRemoveRemoveFormAnswerFile
{
    public function __construct(
        private readonly FormFileUploader $formFileUploader,
        private readonly AnswerFiles $answerFiles,
    ) {
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

        foreach ($this->answerFiles->of($object) as $file) {
            $this->formFileUploader->removeFile($file);
        }
    }
}
