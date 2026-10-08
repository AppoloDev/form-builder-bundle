<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\FormType\FormBuilderType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormBuilderTypeTest extends TestCase
{
    public function testParentIsTheBaseFormType(): void
    {
        self::assertSame(FormType::class, (new FormBuilderType())->getParent());
    }

    public function testEditDefaultsToFalse(): void
    {
        $resolver = new OptionsResolver();
        (new FormBuilderType())->configureOptions($resolver);

        self::assertFalse($resolver->resolve([])['edit']);
        self::assertTrue($resolver->resolve(['edit' => true])['edit']);
    }

    public function testEditMustBeABoolean(): void
    {
        $resolver = new OptionsResolver();
        (new FormBuilderType())->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $resolver->resolve(['edit' => 'yes']);
    }
}
