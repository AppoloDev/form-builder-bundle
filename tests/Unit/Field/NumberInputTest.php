<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\NumberInput;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class NumberInputTest extends TestCase
{
    public function testUsesIntegerTypeByDefault(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1', 'defaultValue' => 3]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', IntegerType::class, self::callback(function (array $options): bool {
            self::assertSame(3.0, $options['data']);

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

    public function testDefaultValueFallsBackToZeroWhenNotConfigured(): void
    {
        $field = new NumberInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'n-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('n-1');
        $formBuilder->expects(self::once())->method('add')->with('n-1', IntegerType::class, self::callback(function (array $options): bool {
            self::assertSame(0, $options['data']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
