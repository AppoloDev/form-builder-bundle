<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Base des champs à choix parmi des options : la valeur stockée est le libellé de l'option choisie
 * (liste de libellés si `multiple`).
 */
abstract class AbstractChoiceField implements FieldInterface
{
    use ValueFieldKind;

    /** @var array<string, mixed> */
    protected array $formOptions = [];
    protected bool $isPrototype = false;

    protected string $id = '';
    protected string $label = '';
    /** @var array<int, array{label: string, isSelected: bool}> */
    protected array $options = [];
    protected bool $required = false;
    protected bool $readOnly = false;
    protected bool $multiple = false;
    protected string $helpText = '';

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
        $this->formOptions = $formOptions;

        return '' !== $this->id;
    }

    /**
     * Options affichées sous forme de radios / cases à cocher plutôt que de liste déroulante.
     */
    abstract protected function isExpanded(): bool;

    /**
     * Options ajoutées en bout de chaîne (liste déroulante : saisie libre, etc.).
     *
     * @param array<string, mixed> $fieldOptions
     *
     * @return array<string, mixed>
     */
    protected function decorateFieldOptions(array $fieldOptions): array
    {
        return $fieldOptions;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $choices = array_map(static fn (array $item): string => $item['label'], $this->options);
        $choices = array_combine($choices, $choices);

        $fieldOptions = [
            'placeholder' => false,
            'label' => $this->label,
            'choices' => $choices,
            'required' => $this->required,
            'disabled' => $this->readOnly,
            'help' => $this->helpText,
            'multiple' => $this->multiple,
            'expanded' => $this->isExpanded(),
            'attr' => [
                'formBuilder' => true,
            ],
        ];

        if (!isset($this->formOptions['edit']) || $this->isPrototype) {
            $selected = array_values(array_filter(
                array_map(static fn (array $item): ?string => $item['isSelected'] ? $item['label'] : null, $this->options),
                static fn (?string $item): bool => null !== $item,
            ));

            $fieldOptions['data'] = $this->multiple ? $selected : ([] === $selected ? null : end($selected));
        }

        if ($this->required) {
            $fieldOptions['constraints'] = [
                new NotBlank(),
            ];
        }

        return $this->decorateFieldOptions($fieldOptions);
    }
}
