<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Base commune aux champs "valeur simple avec valeur par défaut" : gestion du prototype
 * (`__name__`, cf. GenericTextInput), inclusion conditionnelle de `data` selon le mode
 * édition, contrainte `NotBlank` si requis. Contrairement à AbstractSimpleValueField
 * (AddressInput/Signature/Paragraph/Title), ces champs ont une valeur par défaut éditable.
 */
abstract class AbstractValueFieldWithDefault implements FieldInterface
{
    use ValueFieldKind;

    /** @var array<string, mixed> */
    protected array $formOptions = [];
    protected bool $isPrototype = false;

    protected string $id = '';
    protected string $label = '';
    protected string $placeholder = '';
    protected string $defaultValue = '';
    protected bool $required = false;
    protected bool $readOnly = false;
    protected string $helpText = '';

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $this->isPrototype = '__name__' === $formBuilder->getName();
        $formBuilder->add($this->id, $this->getFormType(), $this->getFieldOptions());
        $this->afterAddField($formBuilder);

        return $formBuilder;
    }

    /**
     * @param array<string, mixed> $formOptions
     */
    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->placeholder = $block->configString('placeHolder') ?? '';
        $this->defaultValue = $block->configString('defaultValue') ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        $this->required = $block->configBool('required') ?? false;
        $this->readOnly = $block->configBool('readOnly') ?? false;
        $this->formOptions = $formOptions;

        return '' !== $this->id;
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    abstract protected function getFormType(): string;

    /**
     * Hook pour un post-traitement après l'ajout du champ (ex. DateTimeInput attache un
     * CallbackTransformer sur le FormBuilder retourné par $formBuilder->get($this->id)).
     */
    protected function afterAddField(FormBuilderInterface $formBuilder): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fieldOptions = [
            'label' => $this->label,
            'attr' => [
                'placeholder' => $this->placeholder,
                'formBuilder' => true,
            ],
            'required' => $this->required,
            'disabled' => $this->readOnly,
            'help' => $this->helpText,
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
