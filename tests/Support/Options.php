<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use PHPUnit\Framework\Assert;

/**
 * Accès typé aux options capturées par les mocks de FormBuilderInterface::add().
 */
final class Options
{
    /**
     * Suit $path dans $options en vérifiant à chaque niveau que la valeur est un tableau.
     *
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    public static function at(array $options, string|int ...$path): array
    {
        $current = $options;
        foreach ($path as $key) {
            $current = $current[$key] ?? null;
            Assert::assertIsArray($current);
        }

        return $current;
    }
}
