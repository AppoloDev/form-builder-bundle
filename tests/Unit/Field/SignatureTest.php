<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Enum\FieldKind;
use AppoloDev\FormBuilderBundle\Field\Signature;
use AppoloDev\FormBuilderBundle\FormType\SignatureType;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SignatureTest extends TestCase
{
    public function testGetKindIsValue(): void
    {
        self::assertSame(FieldKind::Value, (new Signature())->getKind());
    }

    public function testValidateDefinitionFailsWithoutId(): void
    {
        self::assertFalse((new Signature())->validateDefinition(FormLayoutBlock::fromArray(['label' => 'Signature']), []));
    }

    public function testAddFieldFromDefinitionAddsSignatureTypeWithOptions(): void
    {
        $field = new Signature();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 'sig-1',
            'label' => 'Signature',
            'required' => true,
            'helpText' => 'Signez ici',
        ]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with(
            'sig-1',
            SignatureType::class,
            self::callback(static function (array $options): bool {
                self::assertSame('Signature', $options['label']);
                self::assertTrue($options['required']);
                self::assertSame('Signez ici', $options['help']);
                $constraints = Options::at($options, 'constraints');
                self::assertCount(1, $constraints);
                self::assertInstanceOf(NotBlank::class, $constraints[0]);

                return true;
            })
        );

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testAddFieldFromDefinitionOmitsConstraintsWhenNotRequired(): void
    {
        $field = new Signature();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'sig-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with(
            'sig-1',
            SignatureType::class,
            self::callback(static function (array $options): bool {
                self::assertArrayNotHasKey('constraints', $options);

                return true;
            })
        );

        $field->addFieldFromDefinition($formBuilder);
    }
}
