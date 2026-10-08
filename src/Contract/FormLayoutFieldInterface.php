<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Contract;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

/**
 * Un champ (ou conteneur) de la structure d'un modèle de formulaire.
 */
interface FormLayoutFieldInterface
{
    public function getId(): ?Uuid;

    public function getFormLayout(): FormLayoutInterface;

    public function setFormLayout(FormLayoutInterface $formLayout): static;

    public function getParent(): ?self;

    public function setParent(?FormLayoutFieldInterface $parent): static;

    /**
     * @return Collection<int, FormLayoutFieldInterface>
     */
    public function getChildren(): Collection;

    public function addChild(FormLayoutFieldInterface $child): static;

    public function removeChild(FormLayoutFieldInterface $child): static;

    public function getFieldKey(): string;

    public function setFieldKey(string $fieldKey): static;

    public function getType(): string;

    public function setType(string $type): static;

    public function getLabel(): ?string;

    public function setLabel(?string $label): static;

    public function getText(): ?string;

    public function setText(?string $text): static;

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): static;

    public function getPosition(): int;

    public function setPosition(int $position): static;
}
