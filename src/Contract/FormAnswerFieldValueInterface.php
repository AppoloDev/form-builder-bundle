<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Contract;

use Symfony\Component\Uid\Uuid;

/**
 * La valeur saisie pour un champ donné (et un index de répétition pour les champs répétables).
 */
interface FormAnswerFieldValueInterface
{
    public function getId(): ?Uuid;

    public function getFormAnswer(): FormAnswerInterface;

    public function setFormAnswer(FormAnswerInterface $formAnswer): static;

    public function getFormLayoutField(): FormLayoutFieldInterface;

    public function setFormLayoutField(FormLayoutFieldInterface $formLayoutField): static;

    public function getRepeatableIndex(): ?int;

    public function setRepeatableIndex(?int $repeatableIndex): static;

    public function getValue(): mixed;

    public function setValue(mixed $value): static;
}
