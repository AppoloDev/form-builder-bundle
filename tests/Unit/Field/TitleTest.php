<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\Title;
use AppoloDev\FormBuilderBundle\FormType\TitleType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

class TitleTest extends TestCase
{
    public function testGetKindIsDisplayOnly(): void
    {
        self::assertSame(FieldKind::DisplayOnly, (new Title())->getKind());
    }

    public function testValidateDefinitionRequiresIdAndText(): void
    {
        $field = new Title();

        self::assertTrue($field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'title-1', 'text' => 'Un titre']), []));
    }

    public function testValidateDefinitionFailsWithoutId(): void
    {
        $field = new Title();

        self::assertFalse($field->validateDefinition(FormLayoutBlock::fromArray(['text' => 'Un titre']), []));
    }

    public function testValidateDefinitionFailsWithoutText(): void
    {
        $field = new Title();

        self::assertFalse($field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'title-1']), []));
    }

    public function testAddFieldFromDefinitionAddsTitleTypeWithTextAsLabel(): void
    {
        $field = new Title();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'title-1', 'text' => 'Un titre']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('title-1', TitleType::class, ['label' => 'Un titre', 'heading' => 'h3']);

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testConfiguredHeadingLevelIsPassedToTheFormType(): void
    {
        $field = new Title();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'title-1', 'text' => 'Un titre', 'heading' => 'h1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('title-1', TitleType::class, ['label' => 'Un titre', 'heading' => 'h1']);

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testAnInvalidHeadingFallsBackToTheDefaultLevel(): void
    {
        $field = new Title();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'title-1', 'text' => 'Un titre', 'heading' => 'h9']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('title-1', TitleType::class, ['label' => 'Un titre', 'heading' => 'h3']);

        $field->addFieldFromDefinition($formBuilder);
    }
}
