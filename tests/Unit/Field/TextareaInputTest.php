<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\TextareaInput;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TextareaInputTest extends TestCase
{
    public function testUsesConfiguredRowsOrDefaultsToFive(): void
    {
        $field = new TextareaInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'ta-1', 'rows' => 8]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('ta-1');
        $formBuilder->expects(self::once())->method('add')->with('ta-1', TextareaType::class, self::callback(static function (array $options): bool {
            self::assertSame(8, Options::at($options, 'attr')['rows']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testDefaultsToFiveRowsWhenNotConfigured(): void
    {
        $field = new TextareaInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'ta-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('ta-1');
        $formBuilder->expects(self::once())->method('add')->with('ta-1', TextareaType::class, self::callback(static function (array $options): bool {
            self::assertSame(5, Options::at($options, 'attr')['rows']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
