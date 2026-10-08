<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Fixtures;

use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Entity\AbstractFormLayout;
use AppoloDev\FormBuilderBundle\Entity\Concern\HasFormLayoutFields;
use Symfony\Component\Uid\Uuid;

/**
 * Entité concrète minimale, comme celle qu'une application hôte écrirait.
 */
final class TestFormLayout extends AbstractFormLayout
{
    use HasFormLayoutFields;

    public function __construct()
    {
        $this->initializeFormLayoutFields();
    }

    public function getId(): ?Uuid
    {
        return null;
    }

    public function createField(): FormLayoutFieldInterface
    {
        return new TestFormLayoutField();
    }
}
