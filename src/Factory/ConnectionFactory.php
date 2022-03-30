<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory;

use InvalidArgumentException;
use Symandy\DatabaseBackupBundle\Model\Connection;

final class ConnectionFactory
{

    public static function create(array $options): Connection
    {
        $driver =
            $options['driver'] ??
            throw new InvalidArgumentException('Connection driver must be configured')
        ;

        /** @var class-string<Connection> $classname */
        $classname = $driver->getConnectionClass();

        return new $classname(...$options['configuration']);
    }

}
