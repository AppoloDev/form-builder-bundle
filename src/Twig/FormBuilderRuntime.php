<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Twig;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFileUrlResolverInterface;
use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class FormBuilderRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormAnswerFileUrlResolverInterface $fileUrlResolver,
        #[Autowire('%form_builder.answers_template%')]
        private readonly string $answersTemplate,
        #[Autowire('%form_builder.answers_pdf_template%')]
        private readonly string $answersPdfTemplate,
    ) {
    }

    /**
     * Affiche les réponses d'un formulaire avec le template configuré (`form_builder.answers_template`,
     * ou `form_builder.answers_pdf_template` si $pdf).
     *
     * @param array<int, array<mixed, mixed>> $answers structure + valeurs, cf. AnswerGenerator::generate()
     */
    public function renderAnswers(array $answers, FormAnswerInterface $formAnswer, bool $pdf = false): string
    {
        return $this->twig
            ->load($pdf ? $this->answersPdfTemplate : $this->answersTemplate)
            ->renderBlock('answers_view', ['answers' => $answers, 'formAnswer' => $formAnswer]);
    }

    public function fileUrl(FormAnswerInterface $formAnswer, string $filename): string
    {
        return $this->fileUrlResolver->resolveDownloadUrl((string) $formAnswer->getId(), $filename);
    }
}
