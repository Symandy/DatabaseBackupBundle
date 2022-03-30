<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterConnectionsPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('symandy_database_backup.registry.connection_registry')) {
            return;
        }

        $definition = $container->getDefinition('symandy_database_backup.registry.connection_registry');

        $connections = $container->getParameter('symandy.connections');

        foreach ($connections as $name => $options) {
            $definition->addMethodCall('registerFromNameAndOptions', [$name, $options]);
        }
    }

}
