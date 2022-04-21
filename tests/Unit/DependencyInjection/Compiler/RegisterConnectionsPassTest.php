<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Unit\DependencyInjection\Compiler;

use Exception;
use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\DependencyInjection\Compiler\RegisterConnectionsPass;
use Symandy\DatabaseBackupBundle\Factory\Backup\BackupFactory;
use Symandy\DatabaseBackupBundle\Factory\Connection\ConnectionFactory;
use Symandy\DatabaseBackupBundle\Factory\Factory;
use Symandy\DatabaseBackupBundle\Model\Backup\Strategy;
use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;
use Symandy\DatabaseBackupBundle\Registry\Backup\BackupRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterConnectionsPassTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $container->register('symandy_database_backup.factory.connection', ConnectionFactory::class);
        $container
            ->register('symandy_database_backup.factory.strategy', Factory::class)
            ->setArgument('$className', Strategy::class)
        ;

        $container
            ->register('symandy_database_backup.factory.backup', BackupFactory::class)
            ->setArgument('$strategyFactory', new Reference('symandy_database_backup.factory.strategy'))
            ->setArgument('$connectionFactory', new Reference('symandy_database_backup.factory.connection'))
        ;

        $container
            ->register('symandy_database_backup.registry.backup_registry', BackupRegistry::class)
            ->setArgument('$backupFactory', new Reference('symandy_database_backup.factory.backup'))
            ->setPublic(true)
        ;
        $container->setParameter('symandy.backups', $this->getConnectionsConfiguration());

        $container->addCompilerPass(new RegisterConnectionsPass());
        $container->compile();

        /** @var BackupRegistry $registry */
        $registry = $container->get('symandy_database_backup.registry.backup_registry');

        self::assertCount(1, $registry->all());
    }

    private function getConnectionsConfiguration(): array
    {
        return [
            'server-1' => [
                'connection' => [
                    'driver' => ConnectionDriver::MySQL,
                    'configuration' => []
                ],
                'strategy' => []
            ]
        ];
    }

}
