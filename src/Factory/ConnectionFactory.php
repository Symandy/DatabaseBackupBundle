<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory;

use InvalidArgumentException;
use Symandy\DatabaseBackupBundle\Model\Connection;
use Symandy\DatabaseBackupBundle\Model\ConnectionDriver;

final class ConnectionFactory
{

    /**
     * @param array<string, int|string|float> $options
     */
    public static function create(array $options): Connection
    {
        /** @var ConnectionDriver $driver */
        $driver =
            $options['driver'] ??
            throw new InvalidArgumentException('Connection driver must be configured')
        ;

        /** @var class-string<Connection> $classname */
        $classname = $driver->getConnectionClass();

        return new $classname(...$options['configuration']);
    }

}
