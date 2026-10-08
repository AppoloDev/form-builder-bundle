<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class NumberInput implements FieldInterface
{
    use ValueFieldKind;

    /** @var array<string, mixed> */
    private array $formOptions = [];
    private bool $isPrototype = false;

    private string $id = '';
    private string $label = '';
    private float|int $defaultValue = 0;
    private bool $required = false;
    private bool $readOnly = false;
    private string $helpText = '';
    private bool $allowDecimal = false;

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $this->isPrototype = '__name__' === $formBuilder->getName();

        $type = NumberType::class;
        if (!$this->allowDecimal) {
            $type = IntegerType::class;
        }

        $formBuilder->add($this->id, $type, $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        $this->required = $block->configBool('required') ?? false;
        $this->readOnly = $block->configBool('readOnly') ?? false;
        $this->allowDecimal = $block->configBool('allowDecimal') ?? false;

        $defaultValue = $block->configNumeric('defaultValue');
        $this->defaultValue = $defaultValue ? (float) $defaultValue : 0;
        $this->formOptions = $formOptions;

        return '' !== $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = [
            'label' => $this->label,
            'required' => $this->required,
            'disabled' => $this->readOnly,
            'help' => $this->helpText,
            'attr' => [
                'formBuilder' => true,
            ],
        ];

        if (!isset($this->formOptions['edit']) || $this->isPrototype) {
            $fieldOptions['data'] = $this->defaultValue;
        }

        if ($this->required) {
            $fieldOptions['constraints'] = [
                new NotBlank(),
            ];
        }

        return $fieldOptions;
    }
}
