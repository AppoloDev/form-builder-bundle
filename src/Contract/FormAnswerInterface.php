<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Contract;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

/**
 * Une réponse à un modèle de formulaire : l'ensemble des valeurs saisies.
 */
interface FormAnswerInterface
{
    public function getId(): ?Uuid;

    public function getFormLayout(): FormLayoutInterface;

    public function setFormLayout(FormLayoutInterface $formLayout): static;

    /**
     * @return Collection<int, FormAnswerFieldValueInterface>
     */
    public function getFieldValues(): Collection;

    public function addFieldValue(FormAnswerFieldValueInterface $fieldValue): static;

    public function removeFieldValue(FormAnswerFieldValueInterface $fieldValue): static;

    /**
     * @return array<string, mixed>
     */
    public function getAnswerData(): array;

    /**
     * @param array<string, mixed> $content
     */
    public function setAnswerData(array $content): static;

    /**
     * Fabrique une valeur vierge de la classe concrète de l'application.
     */
    public function createFieldValue(): FormAnswerFieldValueInterface;
}
