<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\DependencyInjection;

use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function interface_exists;

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
                        ->validate()
                            ->ifTrue(fn (array $v): bool => $v['use_doctrine_url'] && isset($v['connection']))
                            ->thenInvalid('Cannot set "use_doctrine_url" and "connection" for the same configuration')
                        ->end()
                        ->validate()
                            ->ifTrue(fn (array $v): bool => !$v['use_doctrine_url'] && !isset($v['connection']))
                            ->thenInvalid('You should configure the "connection" node if "use_doctrine_bundle" is set to false')
                        ->end()
                        ->validate()
                            ->ifTrue(fn (array $v): bool => $v['use_doctrine_url'] && !interface_exists('Doctrine\DBAL\Driver'))
                            ->thenInvalid('Cannot set "use_doctrine_url" to "true" without "doctrine/dbal". You should install the package with "composer require doctrine/dbal"')
                        ->end()
                        ->children()
                            ->booleanNode('use_doctrine_url')->defaultFalse()->end()
                            ->arrayNode('connection')
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
                                    ->integerNode('max_files')->isRequired()->defaultNull()->end()
                                    ->scalarNode('backup_directory')->isRequired()->defaultNull()->end()
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
