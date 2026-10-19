<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Fabrique de formulaires dont la cible est fournie après coup (dépendance circulaire générateur ↔ types).
 */
final class LateFormFactory implements FormFactoryInterface
{
    private FormFactoryInterface $inner;

    public function set(FormFactoryInterface $inner): void
    {
        $this->inner = $inner;
    }

    /**
     * @param array<mixed> $options
     */
    public function create(string $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', mixed $data = null, array $options = []): FormInterface
    {
        return $this->inner->create($type, $data, $options);
    }

    /**
     * @param array<mixed> $options
     */
    public function createNamed(string $name, string $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', mixed $data = null, array $options = []): FormInterface
    {
        return $this->inner->createNamed($name, $type, $data, $options);
    }

    /**
     * @param array<mixed> $options
     */
    public function createForProperty(string $class, string $property, mixed $data = null, array $options = []): FormInterface
    {
        return $this->inner->createForProperty($class, $property, $data, $options);
    }

    /**
     * @param array<mixed> $options
     */
    public function createBuilder(string $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', mixed $data = null, array $options = []): FormBuilderInterface
    {
        return $this->inner->createBuilder($type, $data, $options);
    }

    /**
     * @param array<mixed> $options
     */
    public function createNamedBuilder(string $name, string $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', mixed $data = null, array $options = []): FormBuilderInterface
    {
        return $this->inner->createNamedBuilder($name, $type, $data, $options);
    }

    /**
     * @param array<mixed> $options
     */
    public function createBuilderForProperty(string $class, string $property, mixed $data = null, array $options = []): FormBuilderInterface
    {
        return $this->inner->createBuilderForProperty($class, $property, $data, $options);
    }
}
