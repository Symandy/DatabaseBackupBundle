<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterConnectionsPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('symandy_database_backup.registry.backup_registry')) {
            return;
        }

        $definition = $container->getDefinition('symandy_database_backup.registry.backup_registry');

        $backups = $container->getParameter('symandy.backups');

        /** @var array<string, array<string, mixed>> $backups */
        foreach ($backups as $name => $options) {
            $definition->addMethodCall('registerFromNameAndOptions', [$name, $options]);
        }

        $container->getParameterBag()->remove('symandy.backups');
    }

}
