<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory\Connection;

use InvalidArgumentException;
use Symandy\DatabaseBackupBundle\Builder\ConfigurationBuilder;
use Symandy\DatabaseBackupBundle\Factory\Factory;
use Symandy\DatabaseBackupBundle\Factory\FactoryInterface;
use Symandy\DatabaseBackupBundle\Model\Connection\Connection;
use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;

/**
 * @implements FactoryInterface<Connection>
 */
final class ConnectionFactory implements FactoryInterface
{
    public function create(array $options): Connection
    {
        $connectionUrl = $options['url'] ?? null;

        if (null !== $connectionUrl) {
            ['driver' => $driver, 'configuration' => $configuration] = ConfigurationBuilder::buildFromUrl($connectionUrl);

            $options['driver'] = $driver;
            $options['configuration'] = $configuration;
        }

        /** @var ConnectionDriver $driver */
        $driver =
            $options['driver'] ??
            throw new InvalidArgumentException('Connection driver must be configured')
        ;

        /** @var class-string<Connection> $classname */
        $classname = $driver->getConnectionClass();

        /** @var FactoryInterface<Connection> $factory */
        $factory = new Factory($classname);

        return $factory->create($options['configuration']);
    }
}
