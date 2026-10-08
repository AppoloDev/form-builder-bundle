<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\FormType\FieldsetType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;

class FieldSet implements FieldInterface
{
    private string $id = '';

    /** @var FormLayoutBlock[] */
    private array $children = [];

    public function getKind(): FieldKind
    {
        return FieldKind::Container;
    }

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, FieldsetType::class, [
            'children' => $this->children,
        ]);

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->children = $block->children;

        return '' !== $this->id;
    }
}
