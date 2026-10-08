<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Base commune aux champs "valeur simple" dont le rendu ne dépend que de
 * id/label/required/helpText — un unique FormType englobant tout le reste
 * (validation côté client, widget, etc.). Ex. Address, Signature.
 */
abstract class AbstractSimpleValueField implements FieldInterface
{
    use ValueFieldKind;

    protected string $id = '';
    protected string $label = '';
    protected bool $required = false;
    protected string $helpText = '';

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, $this->getFormType(), $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        $this->required = $block->configBool('required') ?? false;

        return '' !== $this->id;
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    abstract protected function getFormType(): string;

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = [
            'label' => $this->label,
            'required' => $this->required,
            'help' => $this->helpText,
            'attr' => [
                'formBuilder' => true,
            ],
        ];

        if ($this->required) {
            $fieldOptions['constraints'] = [
                new NotBlank(),
            ];
        }

        return $fieldOptions;
    }
}
