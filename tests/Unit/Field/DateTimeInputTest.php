<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\DateTimeInput;
use AppoloDev\FormBuilderBundle\FormType\DatePickerType;
use AppoloDev\FormBuilderBundle\FormType\DateTimePickerType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;

class DateTimeInputTest extends TestCase
{
    public function testUsesDatePickerTypeWhenOnlyShowDateIsConfigured(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1', 'showDate' => true]), []);

        [$formBuilder, $addedType] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        self::assertSame(DatePickerType::class, $addedType());
    }

    public function testUsesTimeTypeWhenOnlyShowHourIsConfigured(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1', 'showHour' => true]), []);

        [$formBuilder, $addedType] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        self::assertSame(TimeType::class, $addedType());
    }

    public function testUsesDateTimePickerTypeByDefault(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1']), []);

        [$formBuilder, $addedType] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        self::assertSame(DateTimePickerType::class, $addedType());
    }

    public function testTransformerReturnsNullForNullValue(): void
    {
        $transformer = $this->captureTransformer([]);

        self::assertNull($transformer->transform(null));
    }

    public function testTransformerNormalizesSecondsAndMicrosecondsOfADateTime(): void
    {
        $transformer = $this->captureTransformer([]);
        $value = new \DateTime('2026-08-12 10:30:45');

        $result = $transformer->transform($value);

        self::assertSame($value, $result);
        self::assertSame('10:30:00', $result->format('H:i:s'));
    }

    public function testTransformerParsesAnArrayWithADateKey(): void
    {
        $transformer = $this->captureTransformer([]);

        $result = $transformer->transform(['date' => '2026-08-12 10:30:45']);

        self::assertInstanceOf(\DateTime::class, $result);
        self::assertSame('2026-08-12 10:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testReverseTransformerReturnsTheValueAsIs(): void
    {
        $transformer = $this->captureTransformer([]);
        $value = new \DateTime();

        self::assertSame($value, $transformer->reverseTransform($value));
        self::assertNull($transformer->reverseTransform(null));
    }

    public function testFieldOptionsIncludeTheRequiredConstraintWhenRequired(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1', 'required' => true]), []);

        [$formBuilder, , $addedOptions] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        $options = $addedOptions();
        self::assertArrayHasKey('constraints', $options);
        $constraints = $options['constraints'];
        self::assertIsArray($constraints);
        self::assertInstanceOf(\Symfony\Component\Validator\Constraints\NotBlank::class, $constraints[0]);
    }

    public function testFieldOptionsHaveNoConstraintsWhenNotRequired(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1']), []);

        [$formBuilder, , $addedOptions] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        self::assertArrayNotHasKey('constraints', $addedOptions());
    }

    public function testFieldOptionsPresetTheCurrentDateWhenConfiguredOutsideEditMode(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1', 'hasCurrentDate' => true]), []);

        [$formBuilder, , $addedOptions] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        $options = $addedOptions();
        self::assertArrayHasKey('data', $options);
        self::assertInstanceOf(\DateTime::class, $options['data']);
    }

    public function testFieldOptionsOmitDataWhenInEditModeAndNotAPrototype(): void
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1', 'hasCurrentDate' => true]), ['edit' => true]);

        [$formBuilder, , $addedOptions] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        self::assertArrayNotHasKey('data', $addedOptions());
    }

    /**
     * @param array<string, mixed> $formOptions
     */
    private function captureTransformer(array $formOptions): CallbackTransformer
    {
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'dt-1']), $formOptions);

        [$formBuilder, , , $capturedTransformer] = $this->buildFormBuilderStub('dt-1');
        $field->addFieldFromDefinition($formBuilder);

        self::assertInstanceOf(CallbackTransformer::class, $capturedTransformer());

        return $capturedTransformer();
    }

    /**
     * @return array{0: FormBuilderInterface, 1: callable(): ?string, 2: callable(): array<string, mixed>, 3: callable(): ?CallbackTransformer}
     */
    private function buildFormBuilderStub(string $id): array
    {
        $addedType = null;
        $addedOptions = [];
        $capturedTransformer = null;

        $childBuilder = $this->createStub(FormBuilderInterface::class);
        $childBuilder->method('addModelTransformer')->willReturnCallback(function (CallbackTransformer $transformer) use ($childBuilder, &$capturedTransformer): FormBuilderInterface {
            $capturedTransformer = $transformer;

            return $childBuilder;
        });

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn($id);
        $formBuilder->method('get')->willReturn($childBuilder);
        $formBuilder->method('add')->willReturnCallback(function (string $fieldId, string $type, array $options) use ($formBuilder, &$addedType, &$addedOptions): FormBuilderInterface {
            $addedType = $type;
            $addedOptions = $options;

            return $formBuilder;
        });

        return [
            $formBuilder,
            function () use (&$addedType): ?string {
                return $addedType;
            },
            function () use (&$addedOptions): array {
                return $addedOptions;
            },
            function () use (&$capturedTransformer): ?CallbackTransformer {
                return $capturedTransformer;
            },
        ];
    }
}
