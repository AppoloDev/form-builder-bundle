<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class NumberInput implements FieldInterface
{
    use ValueFieldKind;

    /** @var array<string, mixed> */
    private array $formOptions = [];
    private bool $isPrototype = false;

    private string $id = '';
    private string $label = '';
    private string $placeholder = '';
    private float|int|null $defaultValue = null;
    private float|int|null $min = null;
    private float|int|null $max = null;
    private float|int|null $step = null;
    private bool $required = false;
    private bool $readOnly = false;
    private string $helpText = '';
    private bool $allowDecimal = false;

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $this->isPrototype = '__name__' === $formBuilder->getName();

        $formBuilder->add($this->id, $this->isDecimal() ? NumberType::class : IntegerType::class, $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->placeholder = $block->configString('placeHolder') ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        $this->required = $block->configBool('required') ?? false;
        $this->readOnly = $block->configBool('readOnly') ?? false;
        $this->allowDecimal = $block->configBool('allowDecimal') ?? false;
        $this->defaultValue = $block->configNumeric('defaultValue');
        $this->min = $block->configNumeric('min');
        $this->max = $block->configNumeric('max');
        $this->step = $block->configNumeric('step');
        $this->formOptions = $formOptions;

        return '' !== $this->id;
    }

    /**
     * Décimal si le pas n'est pas entier, ou (ancienne config) si `allowDecimal` est activé.
     */
    private function isDecimal(): bool
    {
        if (null !== $this->step) {
            return 0.0 !== fmod((float) $this->step, 1.0);
        }

        return $this->allowDecimal;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $attr = ['formBuilder' => true];
        if ('' !== $this->placeholder) {
            $attr['placeholder'] = $this->placeholder;
        }
        if (null !== $this->min) {
            $attr['min'] = $this->min;
        }
        if (null !== $this->max) {
            $attr['max'] = $this->max;
        }
        if (null !== $this->step) {
            $attr['step'] = $this->step;
        } elseif ($this->isDecimal()) {
            $attr['step'] = 'any';
        }

        $fieldOptions = [
            'label' => $this->label,
            'required' => $this->required,
            'disabled' => $this->readOnly,
            'help' => $this->helpText,
            'attr' => $attr,
        ];

        if (null !== $this->defaultValue && (!isset($this->formOptions['edit']) || $this->isPrototype)) {
            $fieldOptions['data'] = $this->defaultValue;
        }

        $constraints = [];
        if ($this->required) {
            $constraints[] = new NotBlank();
        }
        if (null !== $this->min) {
            $constraints[] = new GreaterThanOrEqual($this->min);
        }
        if (null !== $this->max) {
            $constraints[] = new LessThanOrEqual($this->max);
        }
        if ([] !== $constraints) {
            $fieldOptions['constraints'] = $constraints;
        }

        return $fieldOptions;
    }
}
