<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class FormBuilderBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('upload_path')
                    ->info('Répertoire où sont stockés les fichiers envoyés dans les réponses.')
                    ->defaultValue('%kernel.project_dir%/uploads/form-files/')
                ->end()
            ->end();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->extension('twig', [
            'form_themes' => ['@FormBuilder/form_theme/structure.html.twig'],
        ]);
    }

    /**
     * @param array{upload_path: string} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('form_builder.upload_path', $config['upload_path']);
        $container->import('../config/services.php');
    }
}
