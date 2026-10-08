<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Field;

use AppoloDev\FormBuilderBundle\Field\FieldFactory;
use AppoloDev\FormBuilderBundle\Field\FieldInterface;
use AppoloDev\FormBuilderBundle\Field\TextInput;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FieldFactoryTest extends TestCase
{
    public function testReturnsTheServiceForAKnownType(): void
    {
        $textInput = new TextInput();

        $factory = new FieldFactory($this->buildLocator([
            'AppoloDev\\FormBuilderBundle\\Field\\TextInput' => $textInput,
        ]));

        self::assertSame($textInput, $factory->getField('TextInput'));
    }

    public function testReturnsNullForAnUnknownType(): void
    {
        $factory = new FieldFactory($this->buildLocator([]));

        self::assertNull($factory->getField('SomeFutureFieldType'));
    }

    /**
     * Le type "FieldInterface" (nom de l'interface elle-même) n'est jamais
     * tagué comme service concret : le locator ne le connaît pas, donc
     * plus de risque d'instanciation fatale comme avec l'ancien
     * `new $className()` par concaténation de chaîne.
     */
    public function testReturnsNullForTheInterfaceNameItself(): void
    {
        $factory = new FieldFactory($this->buildLocator([]));

        self::assertNull($factory->getField('FieldInterface'));
    }

    /**
     * @param array<string, FieldInterface> $services
     */
    private function buildLocator(array $services): ContainerInterface
    {
        return new class($services) implements ContainerInterface {
            /**
             * @param array<string, FieldInterface> $services
             */
            public function __construct(private readonly array $services)
            {
            }

            public function get(string $id): mixed
            {
                return $this->services[$id];
            }

            public function has(string $id): bool
            {
                return isset($this->services[$id]);
            }
        };
    }
}
