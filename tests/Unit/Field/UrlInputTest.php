<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\UrlInput;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Url;

class UrlInputTest extends TestCase
{
    public function testAddsHttpsDefaultProtocolAndUrlConstraint(): void
    {
        $field = new UrlInput();
        $field->validateDefinition(FormLayoutBlock::fromArray(['id' => 'u-1']), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('getName')->willReturn('u-1');
        $formBuilder->expects(self::once())->method('add')->with('u-1', UrlType::class, self::callback(function (array $options): bool {
            self::assertSame('https', $options['default_protocol']);
            self::assertCount(1, $options['constraints']);
            self::assertInstanceOf(Url::class, $options['constraints'][0]);

            return true;
        }));

        $field->addFieldFromDefinition($formBuilder);
    }
}
