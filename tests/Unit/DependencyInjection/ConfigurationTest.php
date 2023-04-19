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
                        'driver' => 'unknown',
                    ],
                ],
            ],
        ]]);
    }

    public function testNoConnection(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([[
            'backups' => [
                'test' => [],
            ],
        ]]);
    }

    public function testNoDriver(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [],
                ],
            ],
        ]]);
    }

    public function testDriverValue(): void
    {
        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => 'mysql',
                        'configuration' => [],
                    ],
                    'strategy' => ['max_files' => 1, 'backup_directory' => null],
                ],
            ],
        ]]);

        self::assertEquals(ConnectionDriver::MySQL, $configuration['backups']['test']['connection']['driver']);

        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => ConnectionDriver::MySQL,
                        'configuration' => [],
                    ],
                    'strategy' => ['max_files' => 1, 'backup_directory' => null],
                ],
            ],
        ]]);

        self::assertEquals(ConnectionDriver::MySQL, $configuration['backups']['test']['connection']['driver']);
    }

    public function testInvalidOptions(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Unrecognized option "unknown_parameter" under "symandy_database_backup.backups.test.connection".');

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [
                        'driver' => 'mysql', 'unknown-parameter' => 'test',
                    ],
                ],
            ],
        ]]);
    }

    public function testEmptyConnection(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"url" or "driver" and "configuration" combination should be defined');

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => [],
                ],
            ],
        ]]);
    }

    public function testNoStrategy(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "strategy" under "symandy_database_backup.backups.test" must be configured.');

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => ['url' => ''],
                ],
            ],
        ]]);
    }

    public function testNoMaxFiles(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "max_files" under "symandy_database_backup.backups.test.strategy" must be configured.');

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => ['url' => ''],
                    'strategy' => [],
                ],
            ],
        ]]);
    }

    public function testDateFormatDefaultValue(): void
    {
        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => ['url' => ''],
                    'strategy' => ['max_files' => 1, 'backup_directory' => null],
                ],
            ],
        ]]);

        self::assertEquals('Y-m-d', $configuration['backups']['test']['strategy']['date_format']);
    }

    public function testDateFormatValue(): void
    {
        $configuration = $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => ['url' => ''],
                    'strategy' => [
                        'max_files' => 1,
                        'backup_directory' => null,
                        'date_format' => 'Y-m-d-His',
                    ],
                ],
            ],
        ]]);

        self::assertEquals('Y-m-d-His', $configuration['backups']['test']['strategy']['date_format']);
    }

    public function testNoBackupDirectory(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "backup_directory" under "symandy_database_backup.backups.test.strategy" must be configured.');

        $this->processConfiguration([[
            'backups' => [
                'test' => [
                    'connection' => ['url' => ''],
                    'strategy' => ['max_files' => 1],
                ],
            ],
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
                            'databases' => ['db-1', 'db-2'],
                        ],
                    ],
                    'strategy' => ['max_files' => 1, 'backup_directory' => null],
                ],
                'test2' => [
                    'connection' => [
                        'url' => 'url://host:9999',
                    ],
                    'strategy' => ['max_files' => 1, 'backup_directory' => null, 'date_format' => 'Y-m-d-Hi'],
                ],
            ],
        ]]);

        self::assertNotEmpty($configuration);
        self::assertArrayHasKey('test', $configuration['backups']);
        self::assertArrayHasKey('connection', $configuration['backups']['test']);
        self::assertArrayHasKey('strategy', $configuration['backups']['test']);

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

        $strategy = $configuration['backups']['test']['strategy'];
        self::assertIsArray($strategy);
        self::assertArrayHasKey('max_files', $strategy);
        self::assertArrayHasKey('backup_directory', $strategy);

        self::assertEquals('user-test', $connectionConfiguration['user']);
        self::assertEquals('password-test', $connectionConfiguration['password']);
        self::assertEquals('host-test', $connectionConfiguration['host']);
        self::assertEquals(0000, $connectionConfiguration['port']);

        self::assertContainsEquals('db-1', $connectionConfiguration['databases']);
        self::assertContainsEquals('db-2', $connectionConfiguration['databases']);
        self::assertNotContainsEquals('db-3', $connectionConfiguration['databases']);

        self::assertEquals(1, $strategy['max_files']);
        self::assertNull($strategy['backup_directory']);
        self::assertEquals('Y-m-d', $strategy['date_format']);

        self::assertEquals('url://host:9999', $configuration['backups']['test2']['connection']['url']);
        self::assertNotContains('configuration', $configuration['backups']['test2']['connection']);
        self::assertNotContains('driver', $configuration['backups']['test2']['connection']);
        self::assertEquals(1, $configuration['backups']['test2']['strategy']['max_files']);
        self::assertNull($configuration['backups']['test2']['strategy']['backup_directory']);
        self::assertEquals('Y-m-d-Hi', $configuration['backups']['test2']['strategy']['date_format']);
    }

    private function processConfiguration(array $configs = []): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
