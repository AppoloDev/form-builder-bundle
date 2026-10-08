<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\EmailInput;
use AppoloDev\FormBuilderBundle\Tests\Support\Options;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmailInputTest extends TestCase
{
    public function testAddsEmailConstraintAlongsideNotBlankWhenRequired(): void
    {
        $field = new EmailInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'e-1', 'required' => true]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('e-1');
        $formBuilder->expects(self::once())->method('add')->with('e-1', EmailType::class, self::callback(static function (array $options): bool {
            $constraints = Options::at($options, 'constraints');
            self::assertCount(2, $constraints);
            self::assertInstanceOf(NotBlank::class, $constraints[0]);
            self::assertInstanceOf(Email::class, $constraints[1]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }

    public function testAddsOnlyEmailConstraintWhenNotRequired(): void
    {
        $field = new EmailInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'e-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('e-1');
        $formBuilder->expects(self::once())->method('add')->with('e-1', EmailType::class, self::callback(static function (array $options): bool {
            $constraints = Options::at($options, 'constraints');
            self::assertCount(1, $constraints);
            self::assertInstanceOf(Email::class, $constraints[0]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
