<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model;

final class MySQLConnection implements Connection
{

    public function __construct(
        private readonly string $name,
        private readonly ?string $user = null,
        private readonly ?string $password = null,
        private readonly ?string $host = 'localhost',
        private readonly ?int $port = 3306
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
     * @return array{user: string|null, password: string|null, host: string|null, port: int|null}
     */
    public function getOptions(): array
    {
        return [
            'user' => $this->getUser(),
            'password' => $this->getPassword(),
            'host' => $this->getHost(),
            'port' => $this->getPort()
        ];
    }

}
