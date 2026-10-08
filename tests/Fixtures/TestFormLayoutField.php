<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Fixtures;

use AppoloDev\FormBuilderBundle\Entity\AbstractFormLayoutField;
use AppoloDev\FormBuilderBundle\Entity\Concern\HasFormLayoutFieldChildren;
use Symfony\Component\Uid\Uuid;

final class TestFormLayoutField extends AbstractFormLayoutField
{
    use HasFormLayoutFieldChildren;

    public function __construct()
    {
        $this->initializeFormLayoutFieldChildren();
    }

    public function getId(): ?Uuid
    {
        return null;
    }
}
