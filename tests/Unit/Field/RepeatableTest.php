<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\Repeatable;
use AppoloDev\FormBuilderBundle\FormType\RepeatableType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

class RepeatableTest extends TestCase
{
    public function testGetKindIsRepeatable(): void
    {
        self::assertSame(FieldKind::Repeatable, (new Repeatable())->getKind());
    }

    public function testUsesConfiguredMaxItemsOrDefaultsToFive(): void
    {
        $field = new Repeatable();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'r-1', 'maxItems' => 3]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('r-1', RepeatableType::class, self::callback(function (array $options): bool {
            self::assertSame(3, $options['attr']['maxItems']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testDefaultsToFiveMaxItemsWhenNotConfigured(): void
    {
        $field = new Repeatable();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'r-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with('r-1', RepeatableType::class, self::callback(function (array $options): bool {
            self::assertSame(5, $options['attr']['maxItems']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
