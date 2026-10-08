<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\FormType\RepeatableItemType;
use AppoloDev\FormBuilderBundle\FormType\RepeatableType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;

class Repeatable implements FieldInterface
{
    private string $id = '';

    /** @var FormLayoutBlock[] */
    private array $children = [];
    private int $maxItems = 5;

    public function getKind(): FieldKind
    {
        return FieldKind::Repeatable;
    }

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, RepeatableType::class, $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $maxItems = $block->configNumeric('maxItems');
        $this->maxItems = null !== $maxItems ? (int) $maxItems : 5;
        $this->children = $block->children;

        return '' !== $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        return [
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => RepeatableItemType::class,
            'entry_options' => ['children' => $this->children],
            'attr' => [
                'maxItems' => $this->maxItems,
                'formBuilder' => true,
            ],
        ];
    }
}
