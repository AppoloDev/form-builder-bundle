<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\YesNo;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class YesNoTest extends TestCase
{
    public function testCustomLabelsBecomeTwoExpandedSingleChoices(): void
    {
        $options = $this->addedOptions(['id' => 'f', 'yesLabel' => 'Conforme', 'noLabel' => 'Non conforme', 'multiple' => true]);

        self::assertSame(['Conforme' => 'Conforme', 'Non conforme' => 'Non conforme'], $options['choices']);
        self::assertTrue($options['expanded']);
        self::assertFalse($options['multiple']);
        self::assertSame('yes_no', $options['block_prefix']);
    }

    public function testAnOptionalQuestionHasNoEmptyChoice(): void
    {
        self::assertFalse($this->addedOptions(['id' => 'f', 'yesLabel' => 'Oui', 'noLabel' => 'Non', 'required' => false])['placeholder']);
    }

    public function testBlankAndDuplicateLabelsAreDropped(): void
    {
        self::assertSame(['Oui' => 'Oui'], $this->addedOptions(['id' => 'f', 'yesLabel' => 'Oui', 'noLabel' => ' Oui '])['choices']);
        self::assertSame(['Non' => 'Non'], $this->addedOptions(['id' => 'f', 'yesLabel' => ' ', 'noLabel' => 'Non'])['choices']);
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<mixed>
     */
    private function addedOptions(array $block): array
    {
        $field = new YesNo();
        self::assertTrue($field->validateDefinition(FormLayoutBlock::fromArray(['type' => 'YesNo', ...$block]), []));

        $captured = [];
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('form');
        $formBuilder->expects(self::once())->method('add')
            ->with('f', ChoiceType::class, self::callback(static function (array $options) use (&$captured): bool {
                $captured = $options;

                return true;
            }));
        $field->addFieldFromDefinition($formBuilder);

        return $captured;
    }
}
