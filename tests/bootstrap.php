<?php

declare(strict_types=1);

// Autoload du bundle seul (vendor/ local) ou, quand il est installé en path repository,
// celui de l'application hôte.
foreach ([__DIR__.'/../vendor/autoload.php', __DIR__.'/../../../vendor/autoload.php'] as $autoload) {
    if (is_file($autoload)) {
        require $autoload;

        return;
    }
}

throw new RuntimeException('Impossible de trouver vendor/autoload.php.');
