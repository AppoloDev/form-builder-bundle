<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\Select;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class SelectTest extends TestCase
{
    public function testBuildsChoicesFromOptionsAndSelectsTheSelectedOne(): void
    {
        $field = new Select();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 's-1',
            'options' => [
                ['label' => 'Oui', 'isSelected' => true],
                ['label' => 'Non', 'isSelected' => false],
            ],
        ]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('s-1');
        $formBuilder->expects(self::once())->method('add')->with('s-1', ChoiceType::class, self::callback(function (array $options): bool {
            self::assertSame(['Oui' => 'Oui', 'Non' => 'Non'], $options['choices']);
            self::assertSame('Oui', $options['data']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testKeepsAllSelectedValuesWhenMultiple(): void
    {
        $field = new Select();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 's-1',
            'multiple' => true,
            'options' => [
                ['label' => 'Oui', 'isSelected' => true],
                ['label' => 'Peut-être', 'isSelected' => true],
                ['label' => 'Non', 'isSelected' => false],
            ],
        ]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('s-1');
        $formBuilder->expects(self::once())->method('add')->with('s-1', ChoiceType::class, self::callback(function (array $options): bool {
            self::assertSame(['Oui', 'Peut-être'], array_values($options['data']));

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testAddsAutocompleteAttributesWhenNotCheckCases(): void
    {
        $field = new Select();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 's-1', 'customOption' => true]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('s-1');
        $formBuilder->expects(self::once())->method('add')->with('s-1', ChoiceType::class, self::callback(function (array $options): bool {
            self::assertTrue($options['autocomplete']);
            self::assertTrue($options['allow_options_create']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testOmitsAutocompleteAttributesWhenCheckCases(): void
    {
        $field = new Select();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 's-1', 'checkCases' => true]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('s-1');
        $formBuilder->expects(self::once())->method('add')->with('s-1', ChoiceType::class, self::callback(function (array $options): bool {
            self::assertArrayNotHasKey('autocomplete', $options);
            self::assertTrue($options['expanded']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
