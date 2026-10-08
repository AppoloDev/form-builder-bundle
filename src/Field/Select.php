<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class Select implements FieldInterface
{
    use ValueFieldKind;

    /** @var array<string, mixed> */
    private array $formOptions = [];
    private bool $isPrototype = false;

    private string $id = '';
    private string $label = '';
    /** @var array<int, array{label: string, isSelected: bool}> */
    private array $options = [];
    private bool $required = false;
    private bool $readOnly = false;
    private bool $multiple = false;
    private bool $checkCases = false;
    private bool $customOption = false;
    private string $helpText = '';

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $this->isPrototype = '__name__' === $formBuilder->getName();
        $formBuilder->add($this->id, ChoiceType::class, $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->options = $block->configOptions('options');
        $this->helpText = $block->configString('helpText') ?? '';
        $this->required = $block->configBool('required') ?? false;
        $this->readOnly = $block->configBool('readOnly') ?? false;
        $this->multiple = $block->configBool('multiple') ?? false;
        $this->checkCases = $block->configBool('checkCases') ?? false;
        $this->customOption = $block->configBool('customOption') ?? false;
        $this->formOptions = $formOptions;

        return '' !== $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $choices = array_map(fn (array $item): string => $item['label'], $this->options);
        $choices = array_combine($choices, $choices);

        $fieldOptions = [
            'placeholder' => false,
            'label' => $this->label,
            'choices' => $choices,
            'required' => $this->required,
            'disabled' => $this->readOnly,
            'help' => $this->helpText,
            'multiple' => $this->multiple,
            'expanded' => $this->checkCases,
            'attr' => [
                'formBuilder' => true,
            ],
        ];

        if (!$this->checkCases) {
            $fieldOptions['autocomplete'] = true;
            $fieldOptions['allow_options_create'] = $this->customOption;
        }

        if (!isset($this->formOptions['edit']) || $this->isPrototype) {
            $fieldOptions['data'] = array_filter(
                array_map(fn (array $item) => $item['isSelected'] ? $item['label'] : null, $this->options),
                fn (?string $item): bool => !is_null($item)
            );

            if (!$this->multiple) {
                $fieldOptions['data'] = end($fieldOptions['data']);
            }
        }

        if ($this->required) {
            $fieldOptions['constraints'] = [
                new NotBlank(),
            ];
        }

        return $fieldOptions;
    }
}
