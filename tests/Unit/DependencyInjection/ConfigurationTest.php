<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\DependencyInjection\Configuration;
use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use ValueError;

final class ConfigurationTest extends TestCase
{

    public function testDefaultOptions(): void
    {
        $configuration = $this->processConfiguration();

        self::assertArrayHasKey('backups', $configuration);
        self::assertEmpty($configuration['backups']);
    }

    public function testDriverNotExist(): void
    {
        $this->expectException(ValueError::class);

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => 'unknown'
                    ]
                ]
            ]
        ]]);
    }

    public function testNoConnection(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([[
            'backups' => [
                'test' => []
            ]
        ]]);
    }

    public function testNoDriver(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => []
                ]
            ]
        ]]);
    }

    public function testDriverValue(): void
    {
        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => 'mysql'
                    ]
                ]
            ]
        ]]);

        self::assertEquals(ConnectionDriver::MySQL, $configuration['backups']['test']['connection']['driver']);

        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => ConnectionDriver::MySQL
                    ]
                ]
            ]
        ]]);

        self::assertEquals(ConnectionDriver::MySQL, $configuration['backups']['test']['connection']['driver']);
    }

    public function testInvalidOptions(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => 'mysql', 'unknown-parameter' => 'test'
                    ]
                ]
            ]
        ]]);
    }

    public function testConfigurationValues(): void
    {
        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => ConnectionDriver::MySQL,
                        'configuration' => [
                            'user' => 'user-test',
                            'password' => 'password-test',
                            'host' => 'host-test',
                            'port' => 0000,
                            'databases' => ['db-1', 'db-2']
                        ]
                    ]
                ]
            ]
        ]]);

        self::assertNotEmpty($configuration);
        self::assertArrayHasKey('test', $configuration['backups']);
        self::assertArrayHasKey('connection', $configuration['backups']['test']);

        $connectionNode = $configuration['backups']['test']['connection'];
        self::assertIsArray($connectionNode);
        self::assertArrayHasKey('configuration', $connectionNode);

        $connectionConfiguration = $connectionNode['configuration'];
        self::assertIsArray($connectionConfiguration);
        self::assertArrayHasKey('user', $connectionConfiguration);
        self::assertArrayHasKey('password', $connectionConfiguration);
        self::assertArrayHasKey('host', $connectionConfiguration);
        self::assertArrayHasKey('port', $connectionConfiguration);
        self::assertArrayHasKey('databases', $connectionConfiguration);

        self::assertEquals('user-test', $connectionConfiguration['user']);
        self::assertEquals('password-test', $connectionConfiguration['password']);
        self::assertEquals('host-test', $connectionConfiguration['host']);
        self::assertEquals(0000, $connectionConfiguration['port']);

        self::assertContainsEquals('db-1', $connectionConfiguration['databases']);
        self::assertContainsEquals('db-2', $connectionConfiguration['databases']);
        self::assertNotContainsEquals('db-3', $connectionConfiguration['databases']);
    }

    private function processConfiguration(array $configs = []): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

}
