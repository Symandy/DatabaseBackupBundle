<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry;

use Symandy\DatabaseBackupBundle\Model\Connection\Connection;

interface ConnectionRegistryInterface
{

    /**
     * @return array<string, Connection>
     */
    public function all(): array;

    public function has(string $name): bool;

    public function get(string $name): Connection;

    public function register(string $name, Connection $connection): void;

    /**
     * @param array<string, string|int|float> $options
     */
    public function registerFromNameAndOptions(string $name, array $options): void;

}
