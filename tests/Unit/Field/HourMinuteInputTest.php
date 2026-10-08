<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\HourMinuteInput;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class HourMinuteInputTest extends TestCase
{
    public function testIsAValueField(): void
    {
        self::assertSame(FieldKind::Value, (new HourMinuteInput())->getKind());
    }

    public function testAddsATimeFieldStoringAString(): void
    {
        $options = $this->addedOptions(['id' => 'HourMinuteInput-1', 'label' => 'Heure', 'helpText' => 'HH:MM']);

        self::assertSame('Heure', $options['label']);
        self::assertSame('HH:MM', $options['help']);
        self::assertSame('string', $options['input']);
        self::assertSame('single_text', $options['widget']);
        self::assertFalse($options['with_seconds']);
        self::assertArrayNotHasKey('data', $options);
    }

    public function testRequiredAddsNotBlank(): void
    {
        $options = $this->addedOptions(['id' => 'HourMinuteInput-1', 'required' => true]);

        self::assertInstanceOf(NotBlank::class, Options::at($options, 'constraints')[0]);
    }

    public function testAValidDefaultValueIsPrefilledOutsideEditMode(): void
    {
        self::assertSame('08:30', $this->addedOptions(['id' => 'h', 'defaultValue' => '08:30'])['data']);
        self::assertArrayNotHasKey('data', $this->addedOptions(['id' => 'h', 'defaultValue' => '08:30'], ['edit' => true]));
    }

    public function testAnInvalidDefaultValueIsIgnored(): void
    {
        self::assertArrayNotHasKey('data', $this->addedOptions(['id' => 'h', 'defaultValue' => '25:99']));
        self::assertArrayNotHasKey('data', $this->addedOptions(['id' => 'h', 'defaultValue' => 'soir']));
    }

    public function testReadOnlyDisablesTheField(): void
    {
        self::assertTrue($this->addedOptions(['id' => 'h', 'readOnly' => true])['disabled']);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $formOptions
     *
     * @return array<mixed>
     */
    private function addedOptions(array $config, array $formOptions = []): array
    {
        $field = new HourMinuteInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['type' => 'HourMinuteInput', ...$config]), $formOptions);

        $captured = [];
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('form');
        $formBuilder->expects(self::once())->method('add')->with($config['id'], TimeType::class, self::callback(static function (array $options) use (&$captured): bool {
            $captured = $options;

            return true;
        }));
        $field->addFieldFromDefinition($formBuilder);

        return $captured;
    }
}
