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
            'strategy' => ['max_files' => 1, 'backup_directory' => '/path/to/backup/dir']
        ];

        $backup = new Backup('backup-1', new MySQLConnection(), new Strategy(1, '/path/to/backup/dir'));

        $backupRegistry->registerFromNameAndOptions('backup-1', $options);

        self::assertCount(1, $backupRegistry->all());
        self::assertTrue($backupRegistry->has('backup-1'));
        self::assertEquals($backup, $backupRegistry->get('backup-1'));
    }

}
