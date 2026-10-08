<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Form;

use AppoloDev\FormBuilderBundle\Field\FieldFactory;
use AppoloDev\FormBuilderBundle\Field\FieldInterface;
use AppoloDev\FormBuilderBundle\Form\FormTypeGenerator;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class FormTypeGeneratorTest extends TestCase
{
    public function testAddFieldDelegatesToTheResolvedFieldWhenDefinitionIsValid(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'field-a', 'type' => 'ShortText']);
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $field = $this->createMock(FieldInterface::class);
        $field->expects(self::once())->method('validateDefinition')->with($block, [])->willReturn(true);
        $field->expects(self::once())->method('addFieldFromDefinition')->with($formBuilder);

        $fieldFactory = $this->createStub(FieldFactory::class);
        $fieldFactory->method('getField')->willReturn($field);

        $generator = new FormTypeGenerator($this->createStub(FormFactoryInterface::class), $fieldFactory);

        $generator->addField($formBuilder, $block);
    }

    public function testAddFieldDoesNothingWhenDefinitionIsInvalid(): void
    {
        $block = FormLayoutBlock::fromArray(['type' => 'ShortText']);
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $field = $this->createMock(FieldInterface::class);
        $field->method('validateDefinition')->willReturn(false);
        $field->expects(self::never())->method('addFieldFromDefinition');

        $fieldFactory = $this->createStub(FieldFactory::class);
        $fieldFactory->method('getField')->willReturn($field);

        $generator = new FormTypeGenerator($this->createStub(FormFactoryInterface::class), $fieldFactory);

        $generator->addField($formBuilder, $block);
    }

    public function testAddFieldDoesNothingWhenNoFieldMatchesTheType(): void
    {
        $this->expectNotToPerformAssertions();

        $block = FormLayoutBlock::fromArray(['id' => 'field-a', 'type' => 'UnknownType']);
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $fieldFactory = $this->createStub(FieldFactory::class);
        $fieldFactory->method('getField')->willReturn(null);

        $generator = new FormTypeGenerator($this->createStub(FormFactoryInterface::class), $fieldFactory);

        $generator->addField($formBuilder, $block);
    }

    public function testAddFieldDoesNothingWhenBlockHasNoType(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'field-a']);
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $fieldFactory = $this->createMock(FieldFactory::class);
        $fieldFactory->expects(self::never())->method('getField');

        $generator = new FormTypeGenerator($this->createStub(FormFactoryInterface::class), $fieldFactory);

        $generator->addField($formBuilder, $block);
    }

    public function testAddFieldsProcessesEachChildBlock(): void
    {
        $blockA = FormLayoutBlock::fromArray(['id' => 'field-a', 'type' => 'ShortText']);
        $blockB = FormLayoutBlock::fromArray(['id' => 'field-b', 'type' => 'ShortText']);
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $field = $this->createStub(FieldInterface::class);
        $field->method('validateDefinition')->willReturn(true);

        $fieldFactory = $this->createStub(FieldFactory::class);
        $fieldFactory->method('getField')->willReturn($field);

        $generator = new FormTypeGenerator($this->createStub(FormFactoryInterface::class), $fieldFactory);

        $result = $generator->addFields($formBuilder, [$blockA, $blockB]);

        self::assertSame($formBuilder, $result);
    }
}
