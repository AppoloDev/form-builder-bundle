<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\TelInput;
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
        $formBuilder->expects(self::once())->method('add')->with('t-1', TelType::class, self::callback(function (array $options): bool {
            self::assertCount(2, $options['constraints']);
            self::assertInstanceOf(Regex::class, $options['constraints'][0]);
            self::assertInstanceOf(Length::class, $options['constraints'][1]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
