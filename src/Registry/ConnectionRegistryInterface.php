<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry;

use Symandy\DatabaseBackupBundle\Model\Connection;

interface ConnectionRegistryInterface
{

    public function all(): array;

    public function get(string $name): Connection;

    public function register(string $name, Connection $connection): void;

    public function registerFromNameAndOptions(string $name, array $options): void;

}
