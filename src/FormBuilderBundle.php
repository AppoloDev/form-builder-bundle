<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Contract\FormAnswerInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutFieldInterface;
use AppoloDev\FormBuilderBundle\Contract\FormLayoutInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class FormBuilderBundle extends AbstractBundle
{
    /**
     * Interface du bundle => clé de la configuration `classes` qui désigne l'entité concrète.
     */
    private const ENTITY_INTERFACES = [
        'form_layout' => FormLayoutInterface::class,
        'form_layout_field' => FormLayoutFieldInterface::class,
        'form_answer' => FormAnswerInterface::class,
        'form_answer_field_value' => FormAnswerFieldValueInterface::class,
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('upload_path')
                    ->info('Répertoire où sont stockés les fichiers envoyés dans les réponses.')
                    ->defaultValue('%kernel.project_dir%/uploads/form-files/')
                ->end()
                ->scalarNode('form_theme')
                    ->info('Thème Twig utilisé pour rendre les types propres au form-builder (remplaçable par un autre thème).')
                    ->defaultValue('@FormBuilder/form_theme/shadcn.html.twig')
                ->end()
                ->arrayNode('classes')
                    ->info('Entités concrètes de l\'application, qui étendent les classes de base du bundle.')
                    ->isRequired()
                    ->children()
                        ->scalarNode('form_layout')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('form_layout_field')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('form_answer')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('form_answer_field_value')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $classes = [];
        $formTheme = '@FormBuilder/form_theme/shadcn.html.twig';
        foreach ($builder->getExtensionConfig('form_builder') as $config) {
            if (is_array($config['classes'] ?? null)) {
                $classes = array_merge($classes, $config['classes']);
            }
            if (is_string($config['form_theme'] ?? null)) {
                $formTheme = $config['form_theme'];
            }
        }

        $container->extension('twig', [
            'form_themes' => ['@FormBuilder/form_theme/structure.html.twig', $formTheme],
        ]);

        $resolveTargetEntities = [];
        foreach (self::ENTITY_INTERFACES as $key => $interface) {
            if (isset($classes[$key])) {
                $resolveTargetEntities[$interface] = $classes[$key];
            }
        }

        if ([] !== $resolveTargetEntities) {
            $container->extension('doctrine', [
                'orm' => ['resolve_target_entities' => $resolveTargetEntities],
            ]);
        }
    }

    /**
     * @param array{upload_path: string, form_theme: string, classes: array<string, string>} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('form_builder.upload_path', $config['upload_path']);
        $container->import('../config/services.php');
    }
}
