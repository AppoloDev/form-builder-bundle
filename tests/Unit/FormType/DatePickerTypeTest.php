<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\FormType;

use AppoloDev\FormBuilderBundle\Field\DateTimeInput;
use AppoloDev\FormBuilderBundle\FormType\DatePickerType;
use AppoloDev\FormBuilderBundle\Tests\Support\TestForms;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class DatePickerTypeTest extends TestCase
{
    public function testDatePickerIsASingleNativeInput(): void
    {
        $form = TestForms::factory()->create(DatePickerType::class);

        self::assertSame('single_text', $form->getConfig()->getOption('widget'));
        self::assertCount(0, $form);
    }

    #[DataProvider('modes')]
    public function testEveryDateTimeModeIsRenderedWithoutChoiceFields(string $mode, int $expectedChildren): void
    {
        $builder = TestForms::factory()->createBuilder(FormType::class);
        $field = new DateTimeInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'when', 'mode' => $mode, 'hasCurrentDate' => true]), []);
        $field->addFieldFromDefinition($builder);

        $when = $builder->getForm()->get('when');
        self::assertCount($expectedChildren, $when);
        foreach ($when as $child) {
            self::assertSame('single_text', $child->getConfig()->getOption('widget'));
        }
        self::assertNotNull($when->getData());
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function modes(): iterable
    {
        yield 'date' => ['date', 0];
        yield 'time' => ['time', 0];
        yield 'datetime-local' => ['datetime-local', 2];
    }
}
