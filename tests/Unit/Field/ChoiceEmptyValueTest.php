<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\ChoiceGroup;
use AppoloDev\FormBuilderBundle\Field\Select;
use AppoloDev\FormBuilderBundle\Tests\Support\TestForms;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class ChoiceEmptyValueTest extends TestCase
{
    /**
     * @param class-string<ChoiceGroup|Select> $class
     */
    #[DataProvider('cases')]
    public function testEmptyChoiceOnlyForOptionalSingleChoice(string $class, bool $required, bool $multiple, bool $expectsEmpty): void
    {
        $field = new $class();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 'c',
            'required' => $required,
            'multiple' => $multiple,
            'options' => [['label' => 'A', 'isSelected' => false], ['label' => 'B', 'isSelected' => false]],
        ]), []);
        $builder = TestForms::factory()->createBuilder(FormType::class);
        $field->addFieldFromDefinition($builder);

        $view = $builder->getForm()->createView()['c'];

        self::assertSame($expectsEmpty, null !== $view->vars['placeholder'] && false !== $view->vars['placeholder']);
    }

    /**
     * @return iterable<string, array{class-string, bool, bool, bool}>
     */
    public static function cases(): iterable
    {
        yield 'select optional single' => [Select::class, false, false, true];
        yield 'select required single' => [Select::class, true, false, false];
        yield 'select optional multiple' => [Select::class, false, true, false];
        yield 'radios optional' => [ChoiceGroup::class, false, false, true];
        yield 'radios required' => [ChoiceGroup::class, true, false, false];
        yield 'checkboxes optional' => [ChoiceGroup::class, false, true, false];
    }
}
