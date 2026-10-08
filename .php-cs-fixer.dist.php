<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['vendor', 'node_modules'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@autoPHPUnitMigration:risky' => true,
        'declare_strict_types' => true,
        'phpdoc_types' => false,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder)
;
