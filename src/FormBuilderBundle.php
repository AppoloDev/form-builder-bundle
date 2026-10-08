<?php

declare(strict_types=1);

namespace AppoloDev\FormBuilderBundle;

use AppoloDev\FormBuilderBundle\Contract\FormAnswerFieldValueInterface;
use AppoloDev\FormBuilderBundle\Contract\FormAnswerFileUrlResolverInterface;
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
                ->scalarNode('answers_template')
                    ->info('Template Twig (bloc `answers_view`) qui affiche les réponses d\'un formulaire.')
                    ->defaultValue('@FormBuilder/answers/shadcn.html.twig')
                ->end()
                ->scalarNode('answers_pdf_template')
                    ->info('Template Twig utilisé pour les réponses dans un export PDF (par défaut le même que answers_template).')
                    ->defaultNull()
                ->end()
                ->scalarNode('file_url_resolver')
                    ->info('Id du service (FormAnswerFileUrlResolverInterface) qui fournit l\'URL de téléchargement d\'un fichier de réponse.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('tel')
                    ->info('Validation des champs téléphone.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pattern')->defaultValue('/^\\+?[0-9]+$/')->info('Expression régulière du numéro.')->end()
                        ->integerNode('min_length')->defaultValue(6)->min(0)->end()
                        ->integerNode('max_length')->defaultValue(15)->min(1)->end()
                    ->end()
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
            if (\is_array($config['classes'] ?? null)) {
                $classes = array_merge($classes, $config['classes']);
            }
            if (\is_string($config['form_theme'] ?? null)) {
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
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $answersTemplate = self::stringOption($config, 'answers_template');
        $answersPdfTemplate = $config['answers_pdf_template'] ?? null;

        $container->parameters()
            ->set('form_builder.upload_path', self::stringOption($config, 'upload_path'))
            ->set('form_builder.answers_template', $answersTemplate)
            ->set('form_builder.tel_pattern', self::telOption($config, 'pattern'))
            ->set('form_builder.tel_min_length', self::telOption($config, 'min_length'))
            ->set('form_builder.tel_max_length', self::telOption($config, 'max_length'))
            ->set('form_builder.answers_pdf_template', \is_string($answersPdfTemplate) ? $answersPdfTemplate : $answersTemplate);
        $container->services()->alias(FormAnswerFileUrlResolverInterface::class, self::stringOption($config, 'file_url_resolver'));
        $container->import('../config/services.php');
    }

    /**
     * @param array<mixed> $config
     */
    private static function telOption(array $config, string $key): string|int
    {
        $tel = $config['tel'] ?? null;
        $value = \is_array($tel) ? ($tel[$key] ?? null) : null;
        if (!\is_string($value) && !\is_int($value)) {
            throw new \InvalidArgumentException(\sprintf('La configuration "form_builder.tel.%s" est invalide.', $key));
        }

        return $value;
    }

    /**
     * @param array<mixed> $config
     */
    private static function stringOption(array $config, string $key): string
    {
        $value = $config[$key] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('La configuration "form_builder.%s" doit être une chaîne.', $key));
        }

        return $value;
    }
}
