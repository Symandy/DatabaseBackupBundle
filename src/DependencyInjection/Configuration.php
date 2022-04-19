<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\DependencyInjection;

use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symandy_database_backup');

        $treeBuilder
            ->getRootNode()
            ->fixXmlConfig('backup')
            ->children()
                ->arrayNode('backups')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('connection')
                                ->isRequired()
                                ->children()
                                    ->variableNode('driver')
                                        ->isRequired()
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
                                            ->integerNode('port')->end()
                                            ->arrayNode('databases')
                                                ->scalarPrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('strategy')
                                ->children()
                                    ->integerNode('max_files')->end()
                                    ->scalarNode('output_directory')->end()
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
