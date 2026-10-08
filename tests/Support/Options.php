<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Constraint;

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

    /**
     * Ne garde que les contraintes de validation d'une liste.
     *
     * @param array<mixed> $values
     *
     * @return list<Constraint>
     */
    public static function constraints(array $values): array
    {
        $constraints = [];
        foreach ($values as $value) {
            if ($value instanceof Constraint) {
                $constraints[] = $value;
            }
        }

        return $constraints;
    }
}
