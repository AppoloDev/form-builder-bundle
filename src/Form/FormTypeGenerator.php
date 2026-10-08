<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Form;

use AppoloDev\FormBuilderBundle\Exception\UnknownFieldTypeException;
use AppoloDev\FormBuilderBundle\Field\FieldFactory;
use AppoloDev\FormBuilderBundle\FormType\FormBuilderType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        #[Autowire(param: 'kernel.debug')]
        private readonly bool $debug = false,
        private readonly ?LoggerInterface $logger = null,
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
        if (null === $block->type) {
            return;
        }

        $field = $this->fieldFactory->getField($block->type);

        if (null === $field) {
            $this->reportUnknownType($block->type, $block->id);

            return;
        }

        if ($field->validateDefinition($block, $this->formOptions)) {
            $field->addFieldFromDefinition($formBuilder);
        }
    }

    /**
     * En debug le type inconnu fait échouer le rendu ; en production le champ est ignoré et tracé.
     */
    private function reportUnknownType(string $type, ?string $blockId): void
    {
        if ($this->debug) {
            throw UnknownFieldTypeException::forBlock($type, $blockId);
        }

        $this->logger?->warning('Champ ignoré : type inconnu "{type}" (bloc "{block}").', ['type' => $type, 'block' => $blockId]);
    }
}
