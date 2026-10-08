<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Field;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class FieldFactory
{
    public function __construct(
        #[AutowireLocator('form_builder.field')]
        private readonly ContainerInterface $fields,
    ) {
    }

    public function getField(string $type): ?FieldInterface
    {
        $id = 'AppoloDev\\FormBuilderBundle\\Field\\'.$type;

        if (!$this->fields->has($id)) {
            return null;
        }

        $field = $this->fields->get($id);

        return $field instanceof FieldInterface ? $field : null;
    }
}
