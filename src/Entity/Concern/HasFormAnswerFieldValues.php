<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity\Concern;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Côté inverse de l'association réponse -> valeurs, à utiliser dans l'entité concrète.
 * Appeler initializeFormAnswerFieldValues() depuis le constructeur.
 */
trait HasFormAnswerFieldValues
{
    /**
     * @var Collection<int, FormAnswerFieldValueInterface>
     */
    #[ORM\OneToMany(targetEntity: FormAnswerFieldValueInterface::class, mappedBy: 'formAnswer', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $fieldValues;

    /**
     * @return Collection<int, FormAnswerFieldValueInterface>
     */
    public function getFieldValues(): Collection
    {
        return $this->fieldValues;
    }

    protected function initializeFormAnswerFieldValues(): void
    {
        $this->fieldValues = new ArrayCollection();
    }
}
