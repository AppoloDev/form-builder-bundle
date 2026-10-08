<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Entity;

use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormLayout;
use PHPUnit\Framework\TestCase;

class AbstractFormLayoutTest extends TestCase
{
    public function testSetStructureBuildsFieldsOnAFreshFormLayout(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'field-a', 'type' => 'ShortText', 'label' => 'A'],
        ]);

        self::assertNotNull($formLayout->getFieldByKey('field-a'));
    }

    public function testSetStructureRejectsAFormLayoutThatAlreadyHasFields(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'field-a', 'type' => 'ShortText', 'label' => 'A'],
        ]);

        $this->expectException(\LogicException::class);

        $formLayout->setStructure([
            ['id' => 'field-b', 'type' => 'ShortText', 'label' => 'B'],
        ]);
    }

    public function testSetStructureSkipsBlocksMissingIdOrType(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'field-a', 'type' => 'ShortText', 'label' => 'A'],
            ['type' => 'ShortText', 'label' => 'Sans id'],
            ['id' => 'sans-type', 'label' => 'Sans type'],
        ]);

        self::assertCount(1, $formLayout->getFields());
    }

    public function testGetStructureRoundTripsNestedChildrenAndConfig(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            [
                'id' => 'group',
                'type' => 'Repeatable',
                'label' => 'Groupe',
                'children' => [
                    ['id' => 'child', 'type' => 'ShortText', 'label' => 'Enfant', 'placeholder' => 'Saisir...'],
                ],
            ],
        ]);

        $structure = $formLayout->getStructure();

        self::assertSame('group', $structure[0]['id']);
        self::assertSame('Repeatable', $structure[0]['type']);

        $children = $structure[0]['children'];
        if (!\is_array($children) || !\is_array($children[0])) {
            self::fail('Le bloc "group" devrait avoir un enfant sous forme de tableau.');
        }

        self::assertSame('child', $children[0]['id']);
        self::assertSame('Saisir...', $children[0]['placeholder']);
    }

    public function testGetStructureOrdersBlocksByPosition(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'first', 'type' => 'ShortText', 'label' => 'Premier'],
            ['id' => 'second', 'type' => 'ShortText', 'label' => 'Second'],
        ]);

        $structure = $formLayout->getStructure();

        self::assertSame(['first', 'second'], array_column($structure, 'id'));
    }

    public function testGetFieldByKeyReturnsNullWhenNotFound(): void
    {
        $formLayout = new TestFormLayout();

        self::assertNull($formLayout->getFieldByKey('unknown'));
    }

    public function testGetRepeatableChildFieldFindsFieldNestedInMatchingRepeatable(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            [
                'id' => 'group',
                'type' => 'Repeatable',
                'children' => [
                    ['id' => 'child', 'type' => 'ShortText', 'label' => 'Enfant'],
                ],
            ],
        ]);

        $field = $formLayout->getRepeatableChildField('group', 'child');

        self::assertNotNull($field);
        self::assertSame('child', $field->getFieldKey());
    }

    public function testGetRepeatableChildFieldReturnsNullForNonRepeatableParent(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            [
                'id' => 'group',
                'type' => 'FieldSet',
                'children' => [
                    ['id' => 'child', 'type' => 'ShortText', 'label' => 'Enfant'],
                ],
            ],
        ]);

        self::assertNull($formLayout->getRepeatableChildField('group', 'child'));
    }

    public function testGetRepeatableFieldMapListsChildKeysOfEachRepeatable(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            [
                'id' => 'group',
                'type' => 'Repeatable',
                'children' => [
                    ['id' => 'child-a', 'type' => 'ShortText', 'label' => 'A'],
                    ['id' => 'child-b', 'type' => 'ShortText', 'label' => 'B'],
                ],
            ],
        ]);

        self::assertSame(['group' => ['child-a', 'child-b']], $formLayout->getRepeatableFieldMap());
    }

    public function testHasStructureIsFalseWhenNoFields(): void
    {
        self::assertFalse((new TestFormLayout())->hasStructure());
    }

    public function testHasStructureIsTrueAfterSetStructure(): void
    {
        $formLayout = (new TestFormLayout())->setStructure([
            ['id' => 'field-a', 'type' => 'ShortText', 'label' => 'A'],
        ]);

        self::assertTrue($formLayout->hasStructure());
    }
}
