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
                self::assertCount(3, $constraints);
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
                self::assertCount(2, Options::at($options, 'constraints'));

                return true;
            })
        );

        $field->addFieldFromDefinition($formBuilder);
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function signatureValues(): iterable
    {
        yield 'svg data uri' => ['data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=', true];
        yield 'png data uri' => ['data:image/png;base64,iVBORw0KGgo=', true];
        yield 'remote url' => ['http://169.254.169.254/latest/meta-data', false];
        yield 'local file' => ['file:///etc/passwd', false];
        yield 'html data uri' => ['data:text/html;base64,PHNjcmlwdD4=', false];
        yield 'not base64' => ['data:image/png;base64,<script>', false];
        yield 'too large' => ['data:image/png;base64,'.str_repeat('A', 1_000_001), false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('signatureValues')]
    public function testOnlySmallImageDataUrisAreAccepted(string $value, bool $valid): void
    {
        $field = new Signature();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'sig-1']), []);

        $constraints = [];
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->willReturnCallback(static function (string $name, string $type, array $options) use (&$constraints, $formBuilder): FormBuilderInterface {
            $constraints = Options::at($options, 'constraints');

            return $formBuilder;
        });
        $field->addFieldFromDefinition($formBuilder);

        $violations = \Symfony\Component\Validator\Validation::createValidator()->validate($value, Options::constraints($constraints));

        self::assertSame($valid, 0 === \count($violations));
    }
}
