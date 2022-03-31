<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\DependencyInjection;

use Symandy\DatabaseBackupBundle\Model\ConnectionDriver;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symandy_database_backup');

        $treeBuilder
            ->getRootNode()
            ->fixXmlConfig('connection')
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->variableNode('driver')
                                ->beforeNormalization()
                                ->ifString()
                                ->then(fn(string $v) => ConnectionDriver::from($v))
                                ->end()
                            ->end()
                            ->arrayNode('configuration')
                                ->children()
                                    ->scalarNode('user')->end()
                                    ->scalarNode('password')->end()
                                    ->scalarNode('host')->end()
                                    ->scalarNode('port')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

}
