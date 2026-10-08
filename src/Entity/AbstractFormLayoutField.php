<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base d'un champ de la structure. L'entité concrète doit aussi utiliser
 * {@see Concern\HasFormLayoutFieldChildren}.
 */
#[ORM\MappedSuperclass]
abstract class AbstractFormLayoutField implements FormLayoutFieldInterface
{
    #[ORM\ManyToOne(targetEntity: FormLayoutInterface::class, inversedBy: 'fields')]
    #[ORM\JoinColumn(name: 'form_layout_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected FormLayoutInterface $formLayout;

    #[ORM\ManyToOne(targetEntity: FormLayoutFieldInterface::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?FormLayoutFieldInterface $parent = null;

    #[ORM\Column(type: Types::STRING)]
    protected string $fieldKey = '';

    #[ORM\Column(type: Types::STRING)]
    protected string $type = '';

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $text = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    protected array $config = [];

    #[ORM\Column(type: Types::INTEGER)]
    protected int $position = 0;

    public function getFormLayout(): FormLayoutInterface
    {
        return $this->formLayout;
    }

    public function setFormLayout(FormLayoutInterface $formLayout): static
    {
        $this->formLayout = $formLayout;

        return $this;
    }

    public function getParent(): ?FormLayoutFieldInterface
    {
        return $this->parent;
    }

    public function setParent(?FormLayoutFieldInterface $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function addChild(FormLayoutFieldInterface $child): static
    {
        if (!$this->getChildren()->contains($child)) {
            $this->getChildren()->add($child);
            $child->setParent($this);
            $child->setFormLayout($this->formLayout);
        }

        return $this;
    }

    public function removeChild(FormLayoutFieldInterface $child): static
    {
        if ($this->getChildren()->removeElement($child) && $child->getParent() === $this) {
            $child->setParent(null);
        }

        return $this;
    }

    public function getFieldKey(): string
    {
        return $this->fieldKey;
    }

    public function setFieldKey(string $fieldKey): static
    {
        $this->fieldKey = $fieldKey;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
