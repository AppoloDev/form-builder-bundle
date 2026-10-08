<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Service\FieldKindResolver;
use AppoloDev\FormBuilderBundle\Service\FormLayoutFieldHydrator;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base d'un modèle de formulaire. L'entité concrète de l'application doit aussi utiliser
 * {@see Concern\HasFormLayoutFields} (association OneToMany, interdite dans une superclasse mappée)
 * et fournir {@see createField()}.
 */
#[ORM\MappedSuperclass]
abstract class AbstractFormLayout implements FormLayoutInterface
{
    #[ORM\Column(type: Types::STRING)]
    protected string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getStructure(): array
    {
        return FormLayoutFieldHydrator::buildContent(
            $this->getFields()->filter(static fn (FormLayoutFieldInterface $field): bool => null === $field->getParent())->toArray()
        );
    }

    /**
     * Reconstruit intégralement les champs à partir de zéro — réservé à un modèle sans champs.
     * Sur un modèle déjà répondu, cela recréerait tous les champs avec de nouveaux identifiants,
     * cassant les clés étrangères des valeurs de réponse existantes : utiliser alors
     * {@see \AppoloDev\FormBuilderBundle\UseCase\SyncFormLayoutStructureUseCase}, qui met à jour
     * les champs existants en place.
     *
     * @param array<mixed> $content
     */
    public function setStructure(array $content): static
    {
        if (!$this->getFields()->isEmpty()) {
            throw new \LogicException('setStructure() ne peut être appelé que sur un FormLayout sans champs — utiliser SyncFormLayoutStructureUseCase pour un FormLayout déjà répondu.');
        }

        FormLayoutFieldHydrator::hydrate($this, FormLayoutBlock::listFromArray($content));

        return $this;
    }

    public function addField(FormLayoutFieldInterface $field): static
    {
        $this->getFields()->add($field);

        return $this;
    }

    /**
     * @return array<string, string[]>
     */
    public function getRepeatableFieldMap(): array
    {
        $repeatables = [];
        $blocks = FormLayoutBlock::listFromArray($this->getStructure());
        FormLayoutFieldHydrator::collectRepeatables($blocks, $repeatables);

        return $repeatables;
    }

    public function hasStructure(): bool
    {
        return !$this->getFields()->isEmpty();
    }

    public function getFieldByKey(string $fieldKey): ?FormLayoutFieldInterface
    {
        foreach ($this->getFields() as $field) {
            if ($field->getFieldKey() === $fieldKey) {
                return $field;
            }
        }

        return null;
    }

    public function getRepeatableChildField(string $repeatableKey, string $childKey): ?FormLayoutFieldInterface
    {
        foreach ($this->getFields() as $field) {
            $parent = $field->getParent();
            if (null === $parent) {
                continue;
            }

            if (FieldKind::Repeatable === FieldKindResolver::resolve($parent->getType()) && $parent->getFieldKey() === $repeatableKey && $field->getFieldKey() === $childKey) {
                return $field;
            }
        }

        return null;
    }
}
