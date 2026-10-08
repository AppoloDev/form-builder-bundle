<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Form;

use AppoloDev\FormBuilderBundle\Field\FieldFactory;
use AppoloDev\FormBuilderBundle\FormType\FormBuilderType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormTypeGenerator
{
    /** @var array<string, mixed> */
    private array $formOptions = [];

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FieldFactory $fieldFactory,
    ) {
    }

    /**
     * @param array<int, array<mixed, mixed>> $formStructure
     * @param array<string, mixed>            $data
     * @param array<string, mixed>            $options
     */
    public function buildForm(array $formStructure = [], array $data = [], array $options = []): FormInterface
    {
        $this->formOptions = $options;
        $builder = $this->formFactory->createBuilder(FormBuilderType::class, $data, $options);
        $builder = $this->addFields($builder, FormLayoutBlock::listFromArray($formStructure));

        return $builder->getForm();
    }

    /**
     * @param array<int, array<mixed, mixed>> $formStructure
     * @param array<string, mixed>            $data
     * @param array<string, mixed>            $options
     */
    public function buildNamedForm(string $name, array $formStructure = [], array $data = [], array $options = []): FormInterface
    {
        $this->formOptions = $options;
        $builder = $this->formFactory->createNamedBuilder($name, FormBuilderType::class, $data, $options);
        $builder = $this->addFields($builder, FormLayoutBlock::listFromArray($formStructure));

        return $builder->getForm();
    }

    /**
     * @param FormLayoutBlock[] $children
     */
    public function addFields(FormBuilderInterface $formBuilder, array $children = []): FormBuilderInterface
    {
        foreach ($children as $block) {
            $this->addField($formBuilder, $block);
        }

        return $formBuilder;
    }

    public function addField(FormBuilderInterface $formBuilder, FormLayoutBlock $block): void
    {
        if (null !== $block->type) {
            $field = $this->fieldFactory->getField($block->type);

            if (null !== $field && $field->validateDefinition($block, $this->formOptions)) {
                $field->addFieldFromDefinition($formBuilder);
            }
        }
    }
}
