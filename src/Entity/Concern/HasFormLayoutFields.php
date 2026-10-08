<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity\Concern;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Côté inverse de l'association modèle -> champs, à utiliser dans l'entité concrète
 * (Doctrine interdit un OneToMany dans une superclasse mappée). Appeler
 * initializeFormLayoutFields() depuis le constructeur.
 */
trait HasFormLayoutFields
{
    /**
     * @var Collection<int, FormLayoutFieldInterface>
     */
    #[ORM\OneToMany(targetEntity: FormLayoutFieldInterface::class, mappedBy: 'formLayout', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected Collection $fields;

    /**
     * @return Collection<int, FormLayoutFieldInterface>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    protected function initializeFormLayoutFields(): void
    {
        $this->fields = new ArrayCollection();
    }
}
