<?php

namespace BddArkitect\Extension;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Extension\Extension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Arkitect extension for Behat.
 *
 * This extension provides configuration options for the BDD Arkitect tool.
 */
class ArkitectExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'arkitect';
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Nothing to process
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        // Nothing to initialize
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('project_root')
                    ->defaultValue('%paths.base%')
                    ->info('The root directory of the project')
                ->end()
                ->arrayNode('paths')
                    ->info('Array of relative paths where the tool should analyze and validate rules')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('excludes')
                    ->info('Array of relative paths to exclude from validation')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ignore_errors')
                    ->info('Array of regex patterns to filter errors')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        // Create the configuration service
        $configDefinition = new Definition(ArkitectConfiguration::class, [
            $config['project_root'],
            $config['paths'],
            $config['excludes'],
            $config['ignore_errors']
        ]);
        $container->setDefinition('bdd_arkitect.configuration', $configDefinition);

        // Create the context initializer
        $initializerDefinition = new Definition(ArkitectContextInitializer::class, [
            new Reference('bdd_arkitect.configuration')
        ]);
        $initializerDefinition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition('bdd_arkitect.context_initializer', $initializerDefinition);

        // Register the configuration as a parameter for context initializers
        $container->setParameter('bdd_arkitect.configuration', $config);
    }
}
