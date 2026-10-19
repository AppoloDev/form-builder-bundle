<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormRendererInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFilter;

/**
 * Rend un formulaire avec Twig, le thème Symfony `form_div_layout` (ce que verrait une application sans thème
 * global) puis le thème du bundle. Sert de modèle aux applications qui testent leur propre thème : passer
 * `$themes` pour remplacer le thème du bundle.
 */
final class FormRenderingHarness
{
    public const DEFAULT_THEME = '@FormBuilder/form_theme/default.html.twig';

    private readonly Environment $twig;

    /**
     * @param list<string> $themes thèmes appliqués après form_div_layout, du plus général au plus spécifique
     */
    public function __construct(array $themes = ['@FormBuilder/form_theme/structure.html.twig', self::DEFAULT_THEME])
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__.'/../../templates', 'FormBuilder');
        $loader->addPath(\dirname((string) (new \ReflectionClass(FormExtension::class))->getFileName()).'/../Resources/views/Form');

        $engine = new TwigRendererEngine(['form_div_layout.html.twig', ...$themes], $this->twig = new Environment($loader, ['strict_variables' => true]));
        $renderer = new FormRenderer($engine);

        $this->twig->addExtension(new FormExtension());
        // Sans symfony/translation : filtre `trans` minimal sur les traductions du bundle.
        $translator = new ArrayTranslator();
        $this->twig->addFilter(new TwigFilter('trans', static fn (string $message, array $parameters = [], ?string $domain = null): string => $translator->trans($message, $parameters, $domain)));
        $this->twig->addRuntimeLoader(new FactoryRuntimeLoader([FormRenderer::class => static fn (): FormRendererInterface => $renderer]));
    }

    public function render(FormInterface $form): string
    {
        return $this->twig->createTemplate('{{ form(form) }}')->render(['form' => $form->createView()]);
    }

    public static function dom(string $html): \DOMXPath
    {
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?><body>'.$html.'</body>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new \DOMXPath($document);
    }
}
