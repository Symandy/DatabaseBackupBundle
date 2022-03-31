<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Unit\DependencyInjection\Compiler;

use Exception;
use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\DependencyInjection\Compiler\RegisterConnectionsPass;
use Symandy\DatabaseBackupBundle\Model\ConnectionDriver;
use Symandy\DatabaseBackupBundle\Registry\ConnectionRegistry;
use Symandy\DatabaseBackupBundle\Registry\ConnectionRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterConnectionsPassTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('symandy_database_backup.registry.connection_registry', ConnectionRegistry::class)
            ->setPublic(true)
        ;
        $container->setParameter('symandy.connections', $this->getConnectionsConfiguration());

        $container->addCompilerPass(new RegisterConnectionsPass());
        $container->compile();

        /** @var ConnectionRegistryInterface $registry */
        $registry = $container->get('symandy_database_backup.registry.connection_registry');

        self::assertCount(1, $registry->all());
    }

    private function getConnectionsConfiguration(): array
    {
        return [
            'server-1' => [
                'driver' => ConnectionDriver::MySQL,
                'configuration' => []
            ]
        ];
    }

}
