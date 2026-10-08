<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\Form\FormTypeGenerator;
use AppoloDev\FormBuilderBundle\FormType\RepeatableItemType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepeatableItemTypeTest extends TestCase
{
    public function testBuildFormDelegatesTheFilteredChildrenToTheFormTypeGenerator(): void
    {
        $block = FormLayoutBlock::fromArray(['id' => 'field-a', 'type' => 'ShortText']);
        $formBuilder = self::createStub(FormBuilderInterface::class);

        $formTypeGenerator = $this->createMock(FormTypeGenerator::class);
        $formTypeGenerator->expects(self::once())
            ->method('addFields')
            ->with($formBuilder, [$block])
            ->willReturn($formBuilder);

        $type = new RepeatableItemType($formTypeGenerator);
        $type->buildForm($formBuilder, ['children' => [$block, 'not-a-block']]);
    }

    public function testBuildFormPassesNoChildrenWhenTheOptionIsNotAnArray(): void
    {
        $formBuilder = self::createStub(FormBuilderInterface::class);

        $formTypeGenerator = $this->createMock(FormTypeGenerator::class);
        $formTypeGenerator->expects(self::once())->method('addFields')->with($formBuilder, [])->willReturn($formBuilder);

        $type = new RepeatableItemType($formTypeGenerator);
        $type->buildForm($formBuilder, ['children' => 'not-an-array']);
    }

    public function testConfigureOptionsDefaultsChildrenToAnEmptyArray(): void
    {
        $resolver = new OptionsResolver();
        (new RepeatableItemType(self::createStub(FormTypeGenerator::class)))->configureOptions($resolver);

        self::assertSame([], $resolver->resolve([])['children']);
    }
}
