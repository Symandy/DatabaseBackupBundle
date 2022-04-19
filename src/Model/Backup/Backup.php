<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model\Backup;

use Symandy\DatabaseBackupBundle\Model\Connection\Connection;

class Backup
{

    public function __construct(
        private readonly string $name,
        private readonly Connection $connection,
        private readonly Strategy $strategy
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getStrategy(): Strategy
    {
        return $this->strategy;
    }

}
