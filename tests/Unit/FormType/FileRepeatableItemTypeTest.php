<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\FormType\FileRepeatableItemType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileRepeatableItemTypeTest extends TestCase
{
    public function testBuildFormAddsTheFileFieldWithDefaultsWhenNoFileOptionsGiven(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::exactly(2))->method('add')->willReturnMap([
            ['file', FileType::class, [
                'label' => 'answers.file_label',
                'translation_domain' => 'form_builder_bundle',
                'label_attr' => [],
                'help' => null,
                'required' => false,
                'data_class' => null,
                'constraints' => [],
            ], $formBuilder],
            ['delete', CheckboxType::class, [
                'attr' => ['class' => 'input-delete'],
                'required' => false,
                'row_attr' => ['class' => 'd-none'],
            ], $formBuilder],
        ]);

        (new FileRepeatableItemType())->buildForm($formBuilder, []);
    }

    public function testBuildFormUsesTheProvidedFileOptions(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::exactly(2))->method('add')->willReturnMap([
            ['file', FileType::class, [
                'label' => 'Justificatif',
                'translation_domain' => false,
                'label_attr' => ['class' => 'sr-only'],
                'help' => 'PDF uniquement',
                'required' => true,
                'data_class' => null,
                'constraints' => [],
            ], $formBuilder],
            ['delete', CheckboxType::class, [
                'attr' => ['class' => 'input-delete'],
                'required' => false,
                'row_attr' => ['class' => 'd-none'],
            ], $formBuilder],
        ]);

        (new FileRepeatableItemType())->buildForm($formBuilder, [
            'file_options' => [
                'label' => 'Justificatif',
                'label_attr' => ['class' => 'sr-only'],
                'help' => 'PDF uniquement',
                'required' => true,
            ],
        ]);
    }

    public function testConfigureOptionsDefaultsFileOptionsToAnEmptyArray(): void
    {
        $resolver = new OptionsResolver();
        (new FileRepeatableItemType())->configureOptions($resolver);

        self::assertSame([], $resolver->resolve([])['file_options']);
    }
}
