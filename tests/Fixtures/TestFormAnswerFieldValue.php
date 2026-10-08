<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Fixtures;

use AppoloDev\FormBuilderBundle\Entity\AbstractFormAnswerFieldValue;
use Symfony\Component\Uid\Uuid;

final class TestFormAnswerFieldValue extends AbstractFormAnswerFieldValue
{
    public function getId(): ?Uuid
    {
        return null;
    }
}
