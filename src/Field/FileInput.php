<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\FormType\FileRepeatableItemType;
use AppoloDev\FormBuilderBundle\FormType\FileRepeatableType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class FileInput implements FieldInterface
{
    use ValueFieldKind;

    private string $id = '';
    private string $label = '';
    private int $maxItems = 5;
    private bool $required = false;
    private string $helpText = '';
    private string $acceptedFile = '';

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->add($this->id, FileRepeatableType::class, $this->getFieldOptions());

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        $this->acceptedFile = $block->configString('acceptedFile') ?? '';
        $this->required = $block->configBool('required') ?? false;

        $maxItems = $block->configNumeric('maxItems');
        $this->maxItems = (null === $maxItems || 0.0 === (float) $maxItems) ? 5 : (int) $maxItems;

        return '' !== $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldOptions(): array
    {
        $fileOptions = [
            'required' => $this->required,
            'help' => $this->helpText,
            'label' => $this->label,
        ];

        $fileOptions['constraints'] = match ($this->acceptedFile) {
            'file' => [
                new File(
                    mimeTypes: [
                        'application/pdf',
                    ],
                    mimeTypesMessage: 'validators.form_builder.file_only_pdf',
                ),
            ],
            'image' => [
                new File(
                    mimeTypes: [
                        'image/*',
                    ],
                    mimeTypesMessage: 'validators.form_builder.file_only_image',
                ),
            ],
            'both' => [
                new File(
                    mimeTypes: [
                        'application/pdf', 'image/*',
                    ],
                    mimeTypesMessage: 'validators.form_builder.file_pdf_or_image',
                ),
            ],
            default => [],
        };

        return [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => FileRepeatableItemType::class,
            'entry_options' => [
                'file_options' => $fileOptions,
            ],
            'attr' => [
                'maxItems' => $this->maxItems,
                'formBuilder' => true,
            ],
        ];
    }
}
