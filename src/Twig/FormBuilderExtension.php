<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormBuilderExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('form_builder_answers', [FormBuilderRuntime::class, 'renderAnswers'], ['is_safe' => ['html']]),
            new TwigFunction('form_builder_file_url', [FormBuilderRuntime::class, 'fileUrl']),
        ];
    }
}
