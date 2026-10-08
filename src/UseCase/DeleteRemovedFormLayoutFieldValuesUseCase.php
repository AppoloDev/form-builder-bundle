<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\UseCase;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlockTreeWalker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;

final class DeleteRemovedFormLayoutFieldValuesUseCase
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Supprime explicitement les FormAnswerFieldValue liées aux champs retirés de la structure.
     * Retourne le nombre de valeurs supprimées.
     *
     * @param array<mixed> $newStructure
     */
    public function __invoke(FormLayoutInterface $formLayout, array $newStructure): int
    {
        $oldKeys = $this->collectKeys(FormLayoutBlock::listFromArray($formLayout->getStructure()));
        $newKeys = $this->collectKeys(FormLayoutBlock::listFromArray($newStructure));
        $removedKeys = array_diff($oldKeys, $newKeys);

        if ([] === $removedKeys) {
            return 0;
        }

        $deleted = 0;

        foreach ($formLayout->getFields() as $field) {
            if (!in_array($field->getFieldKey(), $removedKeys, true)) {
                continue;
            }

            $result = $this->entityManager
                ->createQuery(sprintf('DELETE FROM %s fafv WHERE fafv.formLayoutField = :field', FormAnswerFieldValueInterface::class))
                ->setParameter('field', $field->getId(), UuidType::NAME)
                ->execute();

            $deleted += is_numeric($result) ? (int) $result : 0;
        }

        return $deleted;
    }

    /**
     * @param FormLayoutBlock[] $blocks
     *
     * @return string[]
     */
    private function collectKeys(array $blocks): array
    {
        $keys = [];
        FormLayoutBlockTreeWalker::walk($blocks, function (FormLayoutBlock $block) use (&$keys): void {
            if (null !== $block->id) {
                $keys[] = $block->id;
            }
        });

        return $keys;
    }
}
