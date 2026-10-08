<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Contract;

interface FormAnswerFileUrlResolverInterface
{
    public function resolveDownloadUrl(string $formAnswerId, string $filename): string;
}
