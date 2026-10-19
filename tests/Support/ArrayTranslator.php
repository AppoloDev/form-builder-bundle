<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle\Tests\Support;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Traducteur de test lisant les fichiers du bundle (`translations/form_builder_bundle.<locale>.yaml`, indentation
 * de 4 espaces, valeurs entre apostrophes) : pas besoin de symfony/translation ni de symfony/yaml.
 */
final class ArrayTranslator implements TranslatorInterface
{
    /** @var array<string, string> */
    private array $messages = [];

    public function __construct(string $locale = 'fr')
    {
        $path = __DIR__.'/../../translations/form_builder_bundle.'.$locale.'.yaml';
        $stack = [];
        foreach (file($path, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (1 !== preg_match('/^( *)([A-Za-z_]+):(?: (.*))?$/', $line, $m)) {
                continue;
            }
            $depth = intdiv(\strlen($m[1]), 4);
            $stack = \array_slice($stack, 0, $depth);
            $stack[] = $m[2];
            if (isset($m[3]) && '' !== $m[3]) {
                $this->messages[implode('.', $stack)] = trim($m[3], '\'"');
            }
        }
    }

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return strtr($this->messages[$id] ?? $id, $parameters);
    }

    public function getLocale(): string
    {
        return 'fr';
    }
}
