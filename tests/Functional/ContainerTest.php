<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Functional;

use Symandy\DatabaseBackupBundle\Registry\ConnectionRegistryInterface;

final class ContainerTest extends AbstractFunctionalTestCase
{

    public function testContainerHasServices(): void
    {
        $container = self::getContainer();

        self::assertTrue($container->has('symandy_database_backup.registry.connection_registry'));
        self::assertTrue($container->has(ConnectionRegistryInterface::class));
    }

}
