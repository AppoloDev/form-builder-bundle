<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Fixtures;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Entity\AbstractFormAnswer;
use AppoloDev\FormBuilderBundle\Entity\Concern\HasFormAnswerFieldValues;
use Symfony\Component\Uid\Uuid;

final class TestFormAnswer extends AbstractFormAnswer
{
    use HasFormAnswerFieldValues;

    public function __construct()
    {
        $this->initializeFormAnswerFieldValues();
    }

    public function getId(): ?Uuid
    {
        return null;
    }

    public function createFieldValue(): FormAnswerFieldValueInterface
    {
        return new TestFormAnswerFieldValue();
    }
}
