<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\Address;
use AppoloDev\FormBuilderBundle\FormType\AddressType;
use AppoloDev\FormBuilderBundle\ValueObject\FormLayoutBlock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

class AddressTest extends TestCase
{
    public function testAddFieldFromDefinitionAddsAddressTypeWithLabelAndHelp(): void
    {
        $field = new Address();
        $field->validateDefinition(FormLayoutBlock::fromArray([
            'id' => 'addr-1',
            'label' => 'Adresse',
            'helpText' => 'Adresse complète',
        ]), []);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::once())->method('add')->with(
            'addr-1',
            AddressType::class,
            self::callback(static function (array $options): bool {
                self::assertSame('Adresse', $options['label']);
                self::assertSame('Adresse complète', $options['help']);
                self::assertFalse($options['required']);

                return true;
            })
        );

        $field->addFieldFromDefinition($formBuilder);
    }
}
