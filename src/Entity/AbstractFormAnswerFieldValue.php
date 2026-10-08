<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractFormAnswerFieldValue implements FormAnswerFieldValueInterface
{
    #[ORM\ManyToOne(targetEntity: FormAnswerInterface::class, inversedBy: 'fieldValues')]
    #[ORM\JoinColumn(name: 'form_answer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected FormAnswerInterface $formAnswer;

    #[ORM\ManyToOne(targetEntity: FormLayoutFieldInterface::class)]
    #[ORM\JoinColumn(name: 'form_layout_field_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected FormLayoutFieldInterface $formLayoutField;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $repeatableIndex = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected mixed $value = null;

    public function getFormAnswer(): FormAnswerInterface
    {
        return $this->formAnswer;
    }

    public function setFormAnswer(FormAnswerInterface $formAnswer): static
    {
        $this->formAnswer = $formAnswer;

        return $this;
    }

    public function getFormLayoutField(): FormLayoutFieldInterface
    {
        return $this->formLayoutField;
    }

    public function setFormLayoutField(FormLayoutFieldInterface $formLayoutField): static
    {
        $this->formLayoutField = $formLayoutField;

        return $this;
    }

    public function getRepeatableIndex(): ?int
    {
        return $this->repeatableIndex;
    }

    public function setRepeatableIndex(?int $repeatableIndex): static
    {
        $this->repeatableIndex = $repeatableIndex;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }
}
