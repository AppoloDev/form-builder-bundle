<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Contract;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

/**
 * Modèle de formulaire : un titre et une structure (arbre de champs).
 */
interface FormLayoutInterface
{
    public function getId(): ?Uuid;

    public function getTitle(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getStructure(): array;

    /**
     * Reconstruit intégralement les champs — réservé à un modèle sans champs.
     *
     * @param array<mixed> $content
     */
    public function setStructure(array $content): static;

    /**
     * @return Collection<int, FormLayoutFieldInterface>
     */
    public function getFields(): Collection;

    public function addField(FormLayoutFieldInterface $field): static;

    public function hasStructure(): bool;

    public function getFieldByKey(string $fieldKey): ?FormLayoutFieldInterface;

    public function getRepeatableChildField(string $repeatableKey, string $childKey): ?FormLayoutFieldInterface;

    /**
     * @return array<string, string[]>
     */
    public function getRepeatableFieldMap(): array;

    /**
     * Fabrique un champ vierge de la classe concrète de l'application.
     */
    public function createField(): FormLayoutFieldInterface;
}
