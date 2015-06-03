<?php

namespace Partnermarketing\TranslationBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('partnermarketing_translation');

        $rootNode
            ->children()
                ->variableNode('supported_languages')->isRequired()->end()
                ->variableNode('base_language')->isRequired()->end()
                ->arrayNode('one_sky')
                    ->children()
                        ->scalarNode('project_id')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('api_key')
                            ->isRequired()
                        ->end()
                        ->scalarNode('api_secret')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ;

        return $treeBuilder;
    }
}
