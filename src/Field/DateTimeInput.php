<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use AppoloDev\FormBuilderBundle\Field\Concern\ValueFieldKind;
use AppoloDev\FormBuilderBundle\FormType\DatePickerType;
use AppoloDev\FormBuilderBundle\FormType\DateTimePickerType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DateTimeInput implements FieldInterface
{
    use ValueFieldKind;

    /** @var array<string, mixed> */
    private array $formOptions = [];
    private bool $isPrototype = false;

    private string $id = '';
    private string $label = '';
    private bool $required = false;
    private bool $readOnly = false;
    private string $helpText = '';
    private bool $showDate = false;
    private bool $showHour = false;
    private bool $hasCurrentDate = false;

    public function addFieldFromDefinition(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $this->isPrototype = '__name__' === $formBuilder->getName();

        if ($this->showDate && !$this->showHour) {
            $type = DatePickerType::class;
        } elseif (!$this->showDate && $this->showHour) {
            $type = TimeType::class;
        } else {
            $type = DateTimePickerType::class;
        }

        $formBuilder->add($this->id, $type, $this->getFieldOptions());

        $formBuilder->get($this->id)->addModelTransformer(new CallbackTransformer(
            static function (array|\DateTime|null $value): ?\DateTime {
                if (null === $value) {
                    return null;
                }
                if ($value instanceof \DateTime) {
                    $value->setTime((int) $value->format('G'), (int) $value->format('i'), 0);

                    return $value;
                }

                $date = new \DateTime(\is_string($value['date'] ?? null) ? $value['date'] : 'now');
                $date->setTime((int) $date->format('G'), (int) $date->format('i'), 0);

                return $date;
            },
            static function (?\DateTime $value): ?\DateTime {
                return $value;
            }
        ));

        return $formBuilder;
    }

    public function validateDefinition(FormLayoutBlock $block, array $formOptions): bool
    {
        $this->id = $block->id ?? '';
        $this->label = $block->label ?? '';
        $this->helpText = $block->configString('helpText') ?? '';
        $this->required = $block->configBool('required') ?? false;
        $this->readOnly = $block->configBool('readOnly') ?? false;
        $this->showDate = $block->configBool('showDate') ?? false;
        $this->showHour = $block->configBool('showHour') ?? false;
        $this->hasCurrentDate = $block->configBool('hasCurrentDate') ?? false;
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
            $fieldOptions['data'] = $this->hasCurrentDate ? new \DateTime() : null;
        }

        if ($this->required) {
            $fieldOptions['constraints'] = [
                new NotBlank(),
            ];
        }

        return $fieldOptions;
    }
}
