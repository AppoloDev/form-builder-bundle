<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\Field\FieldFactory;
use AppoloDev\FormBuilderBundle\Field\FieldInterface;
use AppoloDev\FormBuilderBundle\Form\FormTypeGenerator;
use AppoloDev\FormBuilderBundle\FormType\ConditionalFieldType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class ConditionalFieldTypeTest extends TestCase
{
    public function testTheConditionalBlockIsWrappedWithTheRuleOfItsOwner(): void
    {
        $blocks = FormLayoutBlock::listFromArray([self::owner()]);
        self::assertCount(2, $blocks);

        $generator = new FormTypeGenerator(self::createStub(FormFactoryInterface::class), $this->factoryWithFields());

        $added = [];
        $formBuilder = self::createStub(FormBuilderInterface::class);
        $formBuilder->method('add')->willReturnCallback(static function (string $name, ?string $type = null, array $options = []) use (&$added, $formBuilder): FormBuilderInterface {
            $added[] = [$name, $type, $options];

            return $formBuilder;
        });

        $generator->addFields($formBuilder, $blocks);

        self::assertCount(2, $added);
        [$name, $type, $options] = $added[1];
        self::assertSame('condition_detail', $name);
        self::assertSame(ConditionalFieldType::class, $type);
        self::assertSame('sel', $options['owner']);
        self::assertSame('is', $options['operator']);
        self::assertSame('Oui', $options['option_label']);
        self::assertInstanceOf(FormLayoutBlock::class, $options['block']);
    }

    public function testAConditionalBlockWhoseOptionWasRemovedIsNotAddedToTheForm(): void
    {
        $owner = self::owner();
        $owner['options'] = [['id' => 'o2', 'label' => 'Non']];
        $blocks = FormLayoutBlock::listFromArray([$owner]);

        $generator = new FormTypeGenerator(self::createStub(FormFactoryInterface::class), $this->factoryWithFields());

        $names = [];
        $formBuilder = self::createStub(FormBuilderInterface::class);
        $formBuilder->method('add')->willReturnCallback(static function (string $name) use (&$names, $formBuilder): FormBuilderInterface {
            $names[] = $name;

            return $formBuilder;
        });

        $generator->addFields($formBuilder, $blocks);

        self::assertSame([], array_diff($names, ['sel']));
    }

    /**
     * @return array<string, mixed>
     */
    private static function owner(): array
    {
        return [
            'id' => 'sel',
            'type' => 'Select',
            'options' => [['id' => 'o1', 'label' => 'Oui']],
            'conditions' => [
                ['id' => 'r1', 'operator' => 'is', 'optionId' => 'o1', 'children' => [['id' => 'detail', 'type' => 'TextInput', 'required' => true]]],
            ],
        ];
    }

    private function factoryWithFields(): FieldFactory
    {
        $field = self::createStub(FieldInterface::class);
        $field->method('validateDefinition')->willReturn(true);
        $field->method('addFieldFromDefinition')->willReturnCallback(static fn (FormBuilderInterface $builder): FormBuilderInterface => $builder->add('sel'));

        $factory = self::createStub(FieldFactory::class);
        $factory->method('getField')->willReturn($field);

        return $factory;
    }
}
