<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\ChoiceGroup;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChoiceGroupTest extends TestCase
{
    public function testIsAValueField(): void
    {
        self::assertSame(FieldKind::Value, (new ChoiceGroup())->getKind());
    }

    public function testRequiresAnId(): void
    {
        self::assertFalse((new ChoiceGroup())->validateDefinition(FormLayoutBlock::fromArray(['type' => 'ChoiceGroup']), []));
    }

    public function testOptionsAreExposedAsExpandedChoicesKeyedByLabel(): void
    {
        $builderOptions = $this->addedOptions([
            'id' => 'ChoiceGroup-1',
            'label' => 'Couleur',
            'helpText' => 'Une seule',
            'options' => [
                ['id' => 'o1', 'label' => 'Rouge', 'value' => 'Rouge'],
                ['id' => 'o2', 'label' => 'Bleu', 'value' => 'Bleu'],
            ],
        ]);

        self::assertTrue($builderOptions['expanded']);
        self::assertFalse($builderOptions['multiple']);
        self::assertSame(['Rouge' => 'Rouge', 'Bleu' => 'Bleu'], $builderOptions['choices']);
        self::assertSame('Couleur', $builderOptions['label']);
        self::assertSame('Une seule', $builderOptions['help']);
        self::assertNull($builderOptions['data']);
        self::assertArrayNotHasKey('autocomplete', $builderOptions);
    }

    public function testMultipleAllowsSeveralChoices(): void
    {
        $builderOptions = $this->addedOptions([
            'id' => 'ChoiceGroup-1',
            'multiple' => true,
            'options' => [['id' => 'o1', 'label' => 'A'], ['id' => 'o2', 'label' => 'B']],
        ]);

        self::assertTrue($builderOptions['multiple']);
        self::assertTrue($builderOptions['expanded']);
        self::assertSame([], $builderOptions['data']);
    }

    public function testRequiredAddsNotBlank(): void
    {
        $builderOptions = $this->addedOptions(['id' => 'ChoiceGroup-1', 'required' => true, 'options' => [['id' => 'o1', 'label' => 'A']]]);

        $constraints = Options::at($builderOptions, 'constraints');
        self::assertCount(1, $constraints);
        self::assertInstanceOf(NotBlank::class, $constraints[0]);
    }

    public function testPreselectedOptionsAreOnlyAppliedOutsideEditMode(): void
    {
        $block = ['id' => 'ChoiceGroup-1', 'options' => [['label' => 'A', 'isSelected' => true], ['label' => 'B']]];

        self::assertSame('A', $this->addedOptions($block)['data']);
        self::assertArrayNotHasKey('data', $this->addedOptions($block, ['edit' => true]));
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $formOptions
     *
     * @return array<mixed>
     */
    private function addedOptions(array $config, array $formOptions = []): array
    {
        $field = new ChoiceGroup();
        $field->validateDefinition(FormLayoutBlock::fromArray(['type' => 'ChoiceGroup', ...$config]), $formOptions);

        $captured = [];
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('form');
        $formBuilder->expects(self::once())->method('add')->with($config['id'], ChoiceType::class, self::callback(static function (array $options) use (&$captured): bool {
            $captured = $options;

            return true;
        }));
        $field->addFieldFromDefinition($formBuilder);

        return $captured;
    }
}
