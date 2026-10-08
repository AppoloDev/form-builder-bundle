<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Entity;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Service\FieldKindResolver;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base d'une réponse. L'entité concrète doit aussi utiliser {@see Concern\HasFormAnswerFieldValues}
 * et fournir {@see createFieldValue()}.
 */
#[ORM\MappedSuperclass]
abstract class AbstractFormAnswer implements FormAnswerInterface
{
    #[ORM\ManyToOne(targetEntity: FormLayoutInterface::class)]
    #[ORM\JoinColumn(name: 'form_layout_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected FormLayoutInterface $formLayout;

    public function getFormLayout(): FormLayoutInterface
    {
        return $this->formLayout;
    }

    public function setFormLayout(FormLayoutInterface $formLayout): static
    {
        $this->formLayout = $formLayout;

        return $this;
    }

    public function addFieldValue(FormAnswerFieldValueInterface $fieldValue): static
    {
        if (!$this->getFieldValues()->contains($fieldValue)) {
            $this->getFieldValues()->add($fieldValue);
            $fieldValue->setFormAnswer($this);
        }

        return $this;
    }

    public function removeFieldValue(FormAnswerFieldValueInterface $fieldValue): static
    {
        $this->getFieldValues()->removeElement($fieldValue);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAnswerData(): array
    {
        $content = [];

        foreach ($this->getFieldValues() as $fieldValue) {
            $field = $fieldValue->getFormLayoutField();
            $parent = $field->getParent();

            if (null === $parent || FieldKind::Repeatable !== FieldKindResolver::resolve($parent->getType())) {
                $content[$field->getFieldKey()] = $fieldValue->getValue();
                continue;
            }

            $repeatableIndex = $fieldValue->getRepeatableIndex() ?? 0;
            $repeatableKey = $parent->getFieldKey();
            if (!isset($content[$repeatableKey]) || !is_array($content[$repeatableKey])) {
                $content[$repeatableKey] = [];
            }
            if (!isset($content[$repeatableKey][$repeatableIndex]) || !is_array($content[$repeatableKey][$repeatableIndex])) {
                $content[$repeatableKey][$repeatableIndex] = [];
            }
            $content[$repeatableKey][$repeatableIndex][$field->getFieldKey()] = $fieldValue->getValue();
        }

        foreach ($content as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                ksort($value);
                $content[$key] = array_values($value);
            }
        }

        return $content;
    }

    /**
     * Remplace les valeurs par celles de $content ; les clés qui ne correspondent à aucun champ
     * du modèle sont ignorées.
     *
     * @param array<string, mixed> $content
     */
    public function setAnswerData(array $content): static
    {
        $this->getFieldValues()->clear();

        foreach ($content as $key => $value) {
            $field = $this->formLayout->getFieldByKey((string) $key);

            if (null !== $field && FieldKind::Repeatable === FieldKindResolver::resolve($field->getType()) && is_array($value)) {
                foreach ($value as $index => $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    foreach ($item as $childKey => $childValue) {
                        $repeatableChildField = $this->formLayout->getRepeatableChildField((string) $key, (string) $childKey);
                        if (null === $repeatableChildField) {
                            continue;
                        }

                        $this->addFieldValue(
                            $this->createFieldValue()
                                ->setFormLayoutField($repeatableChildField)
                                ->setRepeatableIndex((int) $index)
                                ->setValue($childValue)
                        );
                    }
                }
                continue;
            }

            if (null === $field) {
                continue;
            }

            $this->addFieldValue(
                $this->createFieldValue()
                    ->setFormLayoutField($field)
                    ->setValue($value)
            );
        }

        return $this;
    }
}
