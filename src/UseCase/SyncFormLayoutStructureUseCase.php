<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\UseCase;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Doctrine\ORM\EntityManagerInterface;

final class SyncFormLayoutStructureUseCase
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Merge-in-place : met à jour les champs existants, crée les nouveaux,
     * supprime les champs absents du contenu via $em->remove() explicite.
     * Les FormAnswerFieldValue des champs conservés sont préservés.
     *
     * À utiliser pour tout FormLayout déjà répondu — le pendant destructif
     * {@see FormLayoutInterface::setStructure()} refuse
     * désormais d'être appelé sur un FormLayout qui a déjà des champs.
     *
     * @param array<mixed> $content
     */
    public function __invoke(FormLayoutInterface $formLayout, array $content): void
    {
        $existingByKey = [];
        foreach ($formLayout->getFields() as $field) {
            $existingByKey[$field->getFieldKey()] = $field;
        }

        $keptKeys = [];
        $this->processBlocks(FormLayoutBlock::listFromArray($content), null, $existingByKey, $keptKeys, $formLayout);

        foreach ($formLayout->getFields()->toArray() as $field) {
            if (!\in_array($field->getFieldKey(), $keptKeys, true)) {
                $this->entityManager->remove($field);
            }
        }
    }

    /**
     * @param FormLayoutBlock[]                       $blocks
     * @param array<string, FormLayoutFieldInterface> $existingByKey
     * @param string[]                                $keptKeys
     */
    private function processBlocks(
        array $blocks,
        ?FormLayoutFieldInterface $parent,
        array $existingByKey,
        array &$keptKeys,
        FormLayoutInterface $formLayout,
    ): void {
        foreach ($blocks as $index => $block) {
            if (null === $block->id || null === $block->type) {
                continue;
            }

            $key = $block->id;
            $keptKeys[] = $key;

            if (isset($existingByKey[$key])) {
                $field = $existingByKey[$key];
                $field
                    ->setParent($parent)
                    ->setPosition((int) $index)
                    ->setType($block->type)
                    ->setLabel($block->label)
                    ->setText($block->text)
                    ->setConfig($block->config);
            } else {
                $field = $formLayout->createField()
                    ->setFormLayout($formLayout)
                    ->setParent($parent)
                    ->setPosition((int) $index)
                    ->setFieldKey($key)
                    ->setType($block->type)
                    ->setLabel($block->label)
                    ->setText($block->text)
                    ->setConfig($block->config);
                $this->entityManager->persist($field);
            }

            if ([] !== $block->children) {
                $this->processBlocks($block->children, $field, $existingByKey, $keptKeys, $formLayout);
            }
        }
    }
}
