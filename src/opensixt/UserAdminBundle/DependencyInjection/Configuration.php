<?php

namespace opensixt\UserAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opensixt_user_admin');

        $rootNode->children()
                     ->arrayNode('user')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('pagination_limit')
                                ->defaultValue('10')
                            ->end()
                        ->end()
                     ->end()
                     ->arrayNode('group')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('pagination_limit')
                                ->defaultValue('10')
                            ->end()
                        ->end()
                     ->end()
                     ->arrayNode('language')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('pagination_limit')
                                ->defaultValue('10')
                            ->end()
                        ->end()
                     ->end()
                     ->arrayNode('resource')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('pagination_limit')
                                ->defaultValue('10')
                            ->end()
                        ->end()
                     ->end()
                 ->end();

        return $treeBuilder;
    }
}

