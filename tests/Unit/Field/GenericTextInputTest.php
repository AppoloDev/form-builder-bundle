<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\GenericTextInput;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class GenericTextInputTest extends TestCase
{
    public function testValidateDefinitionFailsWithoutId(): void
    {
        self::assertFalse((new GenericTextInput())->validateDefinition(FormLayoutBlock::fromArray(['label' => 'Champ']), []));
    }

    public function testIncludesDefaultValueOutsideEditMode(): void
    {
        $field = new GenericTextInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'f-1', 'defaultValue' => 'valeur']), []);

        $formBuilder = $this->buildFormBuilder(name: 'f-1');
        $formBuilder->expects(self::once())->method('add')->with('f-1', TextType::class, self::callback(static function (array $options): bool {
            self::assertSame('valeur', $options['data']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testOmitsDefaultValueInEditModeWhenNotAPrototype(): void
    {
        $field = new GenericTextInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'f-1', 'defaultValue' => 'valeur']), ['edit' => true]);

        $formBuilder = $this->buildFormBuilder(name: 'f-1');
        $formBuilder->expects(self::once())->method('add')->with('f-1', TextType::class, self::callback(static function (array $options): bool {
            self::assertArrayNotHasKey('data', $options);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testIncludesDefaultValueInEditModeWhenPrototype(): void
    {
        // Le prototype (__name__) d'un Repeatable sert de modèle JS pour les nouvelles lignes
        // ajoutées côté client : il doit garder sa valeur par défaut même en mode édition.
        $field = new GenericTextInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'f-1', 'defaultValue' => 'valeur']), ['edit' => true]);

        $formBuilder = $this->buildFormBuilder(name: '__name__');
        $formBuilder->expects(self::once())->method('add')->with('f-1', TextType::class, self::callback(static function (array $options): bool {
            self::assertSame('valeur', $options['data']);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testRequiredAddsNotBlankConstraint(): void
    {
        $field = new GenericTextInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'f-1', 'required' => true]), []);

        $formBuilder = $this->buildFormBuilder(name: 'f-1');
        $formBuilder->expects(self::once())->method('add')->with('f-1', TextType::class, self::callback(static function (array $options): bool {
            $constraints = Options::at($options, 'constraints');
            self::assertCount(1, $constraints);
            self::assertInstanceOf(NotBlank::class, $constraints[0]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    private function buildFormBuilder(string $name): FormBuilderInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn($name);

        return $formBuilder;
    }
}
