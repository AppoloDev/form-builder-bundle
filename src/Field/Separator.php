<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\FormType\SeparatorType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Trait horizontal entre deux parties du formulaire : affichage pur, sans texte ni valeur (rendu par `separator_row`).
 */
class Separator implements FieldInterface
{
    private string $id = '';

    public function getKind(): FieldKind
    {
        return FieldKind::DisplayOnly;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';

        return '' !== $this->id;
    }

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, SeparatorType::class, ['label' => false]);

        return $formBuilder;
    }
}
