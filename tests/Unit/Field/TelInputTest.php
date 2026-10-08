<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\TelInput;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class TelInputTest extends TestCase
{
    public function testAddsRegexAndLengthConstraints(): void
    {
        $field = new TelInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 't-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('t-1');
        $formBuilder->expects(self::once())->method('add')->with('t-1', TelType::class, self::callback(static function (array $options): bool {
            $constraints = Options::at($options, 'constraints');
            self::assertCount(2, $constraints);
            self::assertInstanceOf(Regex::class, $constraints[0]);
            self::assertInstanceOf(Length::class, $constraints[1]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    /**
     * @return iterable<string, array{string, int<0, max>, int<1, max>, string, bool}>
     */
    public static function phoneValues(): iterable
    {
        yield 'default range, valid' => ['0612345678', 6, 15, '/^\\+?[0-9]+$/', true];
        yield 'default range, international' => ['+33612345678', 6, 15, '/^\\+?[0-9]+$/', true];
        yield 'too short' => ['12345', 6, 15, '/^\\+?[0-9]+$/', false];
        yield 'too long' => ['1234567890123456', 6, 15, '/^\\+?[0-9]+$/', false];
        yield 'letters' => ['06abcdefgh', 6, 15, '/^\\+?[0-9]+$/', false];
        yield 'exact length, valid' => ['0612345678', 10, 10, '/^\\+?[0-9]+$/', true];
        yield 'exact length, 9 digits' => ['061234567', 10, 10, '/^\\+?[0-9]+$/', false];
        yield 'custom pattern' => ['06 12 34 56 78', 6, 15, '/^[0-9 ]+$/', true];
    }

    /**
     * @param int<0, max> $min
     * @param int<1, max> $max
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('phoneValues')]
    public function testLengthAndPatternAreConfigurable(string $value, int $min, int $max, string $pattern, bool $valid): void
    {
        $field = new TelInput($pattern, $min, $max);
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 't-1']), []);

        $constraints = [];
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('t-1');
        $formBuilder->expects(self::once())->method('add')->willReturnCallback(static function (string $name, string $type, array $options) use (&$constraints, $formBuilder): FormBuilderInterface {
            $constraints = Options::at($options, 'constraints');

            return $formBuilder;
        });
        $field->addFieldFromDefinition($formBuilder);

        $violations = \Symfony\Component\Validator\Validation::createValidator()->validate($value, Options::constraints($constraints));

        self::assertSame($valid, 0 === \count($violations));
    }
}
