<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model;

enum ConnectionDriver: string
{

    case MySQL = 'mysql';

    /**
     * @return class-string
     */
    public function getConnectionClass(): string
    {
        return match ($this) {
            self::MySQL => MySQLConnection::class
        };
    }

}
