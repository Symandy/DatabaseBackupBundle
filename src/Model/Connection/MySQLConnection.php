<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model\Connection;

final class MySQLConnection implements Connection
{

    /**
     * @param array<int, string> $databases
     */
    public function __construct(
        private readonly string $name,
        private readonly ?string $user = null,
        private readonly ?string $password = null,
        private readonly ?string $host = '127.0.0.1',
        private readonly ?int $port = 3306,
        private readonly array $databases = []
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @return array<int, string>
     */
    public function getDatabases(): array
    {
        return $this->databases;
    }

    /**
     * @return array{user: string|null, password: string|null, host: string|null, port: int|null, databases: array<int, string>}
     */
    public function getOptions(): array
    {
        return [
            'user' => $this->getUser(),
            'password' => $this->getPassword(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'databases' => $this->getDatabases()
        ];
    }

}
