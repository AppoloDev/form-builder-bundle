<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity\Concern;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Côté inverse de l'association champ -> champs enfants, à utiliser dans l'entité concrète.
 * Appeler initializeFormLayoutFieldChildren() depuis le constructeur.
 */
trait HasFormLayoutFieldChildren
{
    /**
     * @var Collection<int, FormLayoutFieldInterface>
     */
    #[ORM\OneToMany(targetEntity: FormLayoutFieldInterface::class, mappedBy: 'parent', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected Collection $children;

    /**
     * @return Collection<int, FormLayoutFieldInterface>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    protected function initializeFormLayoutFieldChildren(): void
    {
        $this->children = new ArrayCollection();
    }
}
