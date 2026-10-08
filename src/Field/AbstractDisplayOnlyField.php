<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Base commune aux champs d'affichage pur (pas de valeur saisie), dont le
 * rendu ne dépend que de id/text. Ex. Title, Paragraph.
 */
abstract class AbstractDisplayOnlyField implements FieldInterface
{
    protected string $id = '';
    protected string $text = '';

    public function getKind(): FieldKind
    {
        return FieldKind::DisplayOnly;
    }

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, $this->getFormType(), [
            'label' => $this->text,
        ]);

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->text = $block->text ?? '';

        return '' !== $this->id && '' !== $this->text;
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    abstract protected function getFormType(): string;
}
