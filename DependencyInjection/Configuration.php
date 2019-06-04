<?php

namespace Evirma\Bundle\CoreOauthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('core_oauth');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('core_oauth');
        }
        $rootNode
            ->children()
                ->scalarNode('redirect_uri_route')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
            ->end()
            ->append($this->addServicesSection())
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function addServicesSection()
    {
        $tree = new TreeBuilder('services');
        $node = $tree->getRootNode();
        $node
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('title')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('resource_owner')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->variableNode('options')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ->end();
        return $node;
    }
}
