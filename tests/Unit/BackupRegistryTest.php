<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Unit;

use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\Factory\Backup\BackupFactory;
use Symandy\DatabaseBackupBundle\Factory\Connection\ConnectionFactory;
use Symandy\DatabaseBackupBundle\Factory\Factory;
use Symandy\DatabaseBackupBundle\Model\Backup\Backup;
use Symandy\DatabaseBackupBundle\Model\Backup\Strategy;
use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;
use Symandy\DatabaseBackupBundle\Model\Connection\MySQLConnection;
use Symandy\DatabaseBackupBundle\Registry\Backup\BackupRegistry;

final class BackupRegistryTest extends TestCase
{
    public function testRegisterFromNameAndOptions(): void
    {
        $backupFactory = new BackupFactory(new ConnectionFactory(), new Factory(Strategy::class));
        $backupRegistry = new BackupRegistry($backupFactory);

        $options = [
            'connection' => ['driver' => ConnectionDriver::MySQL, 'configuration' => []],
            'strategy' => ['max_files' => 1, 'backup_directory' => '/path/to/backup/dir'],
        ];

        $backup = new Backup('backup-1', new MySQLConnection(), new Strategy(1, '/path/to/backup/dir'));

        $backupRegistry->registerFromNameAndOptions('backup-1', $options);

        self::assertCount(1, $backupRegistry->all());
        self::assertTrue($backupRegistry->has('backup-1'));
        self::assertEquals($backup, $backupRegistry->get('backup-1'));
    }

    public function testRegisterFromNameAndURL(): void
    {
        $backupFactory = new BackupFactory(new ConnectionFactory(), new Factory(Strategy::class));
        $backupRegistry = new BackupRegistry($backupFactory);

        $options = [
            'connection' => ['url' => 'mysql://user:password@hostname:9999/db_1'],
            'strategy' => ['max_files' => 1, 'backup_directory' => '/path/to/backup/dir'],
        ];

        $expectedBackup = new Backup(
            'backup-1',
            new MySQLConnection(user: 'user', password: 'password', host: 'hostname', port: 9999, databases: ['db_1']),
            new Strategy(1, '/path/to/backup/dir')
        );

        $backupRegistry->registerFromNameAndOptions('backup-1', $options);

        self::assertCount(1, $backupRegistry->all());
        self::assertTrue($backupRegistry->has('backup-1'));
        self::assertEquals($expectedBackup, $backupRegistry->get('backup-1'));
    }

    public function testRegisterURLOverridesConfigurationArray(): void
    {
        $backupFactory = new BackupFactory(new ConnectionFactory(), new Factory(Strategy::class));
        $backupRegistry = new BackupRegistry($backupFactory);

        $options = [
            'connection' => [
                'url' => 'mysql://user:password@hostname:9999/db_1',
                'driver' => ConnectionDriver::MySQL,
                'configuration' => [
                    'user' => 'user2',
                    'password' => 'secret',
                    'host' => 'remote',
                    'port' => 9998,
                    'databases' => ['db_2'],
                ],
            ],
            'strategy' => ['max_files' => 1, 'backup_directory' => '/path/to/backup/dir'],
        ];

        $expectedBackup = new Backup(
            'backup-1',
            new MySQLConnection(user: 'user', password: 'password', host: 'hostname', port: 9999, databases: ['db_1']),
            new Strategy(1, '/path/to/backup/dir')
        );

        $backupRegistry->registerFromNameAndOptions('backup-1', $options);

        self::assertCount(1, $backupRegistry->all());
        self::assertTrue($backupRegistry->has('backup-1'));
        self::assertEquals($expectedBackup, $backupRegistry->get('backup-1'));
    }
}
