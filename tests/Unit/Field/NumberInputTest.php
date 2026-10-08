<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\NumberInput;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class NumberInputTest extends TestCase
{
    public function testUsesIntegerTypeByDefault(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1', 'defaultValue' => 3]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', IntegerType::class, self::callback(static function (array $options): bool {
            self::assertSame(3, $options['data']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testUsesNumberTypeWhenDecimalAllowed(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1', 'allowDecimal' => true]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', NumberType::class, self::anything());

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testNoDefaultValueIsForcedWhenNotConfigured(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', IntegerType::class, self::callback(static function (array $options): bool {
            self::assertArrayNotHasKey('data', $options);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testMinMaxStepPlaceholderAreExposedAsAttributesAndConstraints(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 'n-1',
            'placeHolder' => 'Quantité',
            'min' => '1',
            'max' => '10',
            'step' => '1',
        ]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', IntegerType::class, self::callback(static function (array $options): bool {
            $attr = Options::at($options, 'attr');
            self::assertSame('Quantité', $attr['placeholder']);
            self::assertSame(1.0, $attr['min']);
            self::assertSame(10.0, $attr['max']);
            self::assertSame(1.0, $attr['step']);

            $constraints = Options::at($options, 'constraints');
            self::assertCount(2, $constraints);
            self::assertInstanceOf(GreaterThanOrEqual::class, $constraints[0]);
            self::assertInstanceOf(LessThanOrEqual::class, $constraints[1]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testAFractionalStepSelectsTheDecimalType(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1', 'step' => '0.5']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', NumberType::class, self::callback(static function (array $options): bool {
            self::assertSame(0.5, Options::at($options, 'attr')['step']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testAnIntegerStepWinsOverLegacyAllowDecimal(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1', 'step' => '1', 'allowDecimal' => true]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', IntegerType::class, self::anything());

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testLegacyAllowDecimalWithoutStepUsesStepAny(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1', 'allowDecimal' => true]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', NumberType::class, self::callback(static function (array $options): bool {
            self::assertSame('any', Options::at($options, 'attr')['step']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
