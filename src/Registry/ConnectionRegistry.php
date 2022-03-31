<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry;

use Symandy\DatabaseBackupBundle\Factory\ConnectionFactory;
use Symandy\DatabaseBackupBundle\Model\Connection;

final class ConnectionRegistry implements ConnectionRegistryInterface
{

    /** @var array<string, Connection> */
    private array $registry = [];

    /**
     * @return array<string, Connection>
     */
    public function all(): array
    {
        return $this->registry;
    }

    public function get(string $name): Connection
    {
        return $this->registry[$name] ?? throw new \InvalidArgumentException("Connection $name does not exists");
    }

    public function register(string $name, Connection $connection): void
    {
        $this->registry[$name] = $connection;
    }

    public function registerFromNameAndOptions(string $name, array $options): void
    {
        $this->register($name, ConnectionFactory::create($options));
    }

}
