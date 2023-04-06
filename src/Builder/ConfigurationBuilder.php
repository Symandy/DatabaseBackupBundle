<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Builder;

use InvalidArgumentException;
use Nyholm\Dsn\DsnParser;
use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;

use function substr;

final class ConfigurationBuilder
{
    /**
     * @return array{driver: ConnectionDriver, configuration: array{host?: string, port?: int, user: string|null, password: string|null, databases: array<non-empty-string>}}
     */
    public static function buildFromUrl(string $url): array
    {
        $urlParsing = DsnParser::parse($url);

        $scheme =
            $urlParsing->getScheme() ??
            throw new InvalidArgumentException('Could not parse the scheme from the url')
        ;

        $driver =
            ConnectionDriver::tryFrom($scheme) ??
            throw new InvalidArgumentException("Driver $scheme is not supported")
        ;

        if ((null === $path = $urlParsing->getPath()) || '' === substr($path, 1)) {
            throw new InvalidArgumentException('Database could not be parsed');
        }

        $configuration = [
            'user' => $urlParsing->getUser(),
            'password' => $urlParsing->getPassword(),
            'databases' => [substr($path, 1)],
        ];

        if (null !== $urlParsing->getHost()) {
            $configuration['host'] = $urlParsing->getHost();
        }

        if (null !== $urlParsing->getPort()) {
            $configuration['port'] = $urlParsing->getPort();
        }

        return [
            'driver' => $driver,
            'configuration' => $configuration
        ];
    }
}
