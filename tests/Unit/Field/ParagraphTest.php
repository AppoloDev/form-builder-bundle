<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\Paragraph;
use AppoloDev\FormBuilderBundle\FormType\ParagraphType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

class ParagraphTest extends TestCase
{
    public function testGetKindIsDisplayOnly(): void
    {
        self::assertSame(FieldKind::DisplayOnly, (new Paragraph())->getKind());
    }

    public function testAddFieldFromDefinitionAddsParagraphTypeWithTextAsLabel(): void
    {
        $field = new Paragraph();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'p-1', 'text' => 'Un paragraphe']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('p-1', ParagraphType::class, ['label' => 'Un paragraphe']);

        $field->addFieldFromDefinition($formBuilder);
    }
}
