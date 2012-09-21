<?php

namespace Butterweed\SF1EmbedderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('butterweed_sf1_embedder');

        $rootNode
            ->children()
                ->arrayNode('embbeds')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('prefix')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('app')->isRequired()->end()
                            ->scalarNode('path')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
