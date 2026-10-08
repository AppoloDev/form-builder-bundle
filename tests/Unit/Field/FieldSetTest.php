<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\FieldSet;
use AppoloDev\FormBuilderBundle\FormType\FieldsetType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

class FieldSetTest extends TestCase
{
    public function testGetKindIsContainer(): void
    {
        self::assertSame(FieldKind::Container, (new FieldSet())->getKind());
    }

    public function testValidateDefinitionFailsWithoutId(): void
    {
        self::assertFalse((new FieldSet())->validateDefinition(FormLayoutBlock::fromArray([]), []));
    }

    public function testAddFieldFromDefinitionPassesChildrenThrough(): void
    {
        $field = new FieldSet();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 'fs-1',
            'children' => [['id' => 'child', 'type' => 'ShortText']],
        ]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('fs-1', FieldsetType::class, self::callback(function (array $options): bool {
            self::assertCount(1, $options['children']);
            self::assertSame('child', $options['children'][0]->id);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
