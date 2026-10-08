<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Unit\Twig;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFileUrlResolverInterface;
use AppoloDev\FormBuilderBundle\Tests\Fixtures\TestFormAnswer;
use AppoloDev\FormBuilderBundle\Twig\FormBuilderRuntime;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class FormBuilderRuntimeTest extends TestCase
{
    public function testRenderAnswersUsesTheWebTemplateByDefault(): void
    {
        $runtime = $this->runtime();

        $html = $runtime->renderAnswers([['label' => 'Nom']], new TestFormAnswer());

        self::assertSame('web:1', $html);
    }

    public function testRenderAnswersUsesThePdfTemplateWhenAsked(): void
    {
        $runtime = $this->runtime();

        $html = $runtime->renderAnswers([['label' => 'Nom']], new TestFormAnswer(), true);

        self::assertSame('pdf:1', $html);
    }

    public function testFileUrlDelegatesToTheResolver(): void
    {
        $resolver = $this->createMock(FormAnswerFileUrlResolverInterface::class);
        $resolver->expects(self::once())->method('resolveDownloadUrl')->with('', 'file.pdf')->willReturn('https://example.test/file.pdf');

        $url = $this->runtime($resolver)->fileUrl(new TestFormAnswer(), 'file.pdf');

        self::assertSame('https://example.test/file.pdf', $url);
    }

    private function runtime(?FormAnswerFileUrlResolverInterface $resolver = null): FormBuilderRuntime
    {
        $twig = new Environment(new ArrayLoader([
            'web.twig' => '{% block answers_view %}web:{{ answers|length }}{% endblock %}',
            'pdf.twig' => '{% block answers_view %}pdf:{{ answers|length }}{% endblock %}',
        ]));

        return new FormBuilderRuntime($twig, $resolver ?? self::createStub(FormAnswerFileUrlResolverInterface::class), 'web.twig', 'pdf.twig');
    }
}
