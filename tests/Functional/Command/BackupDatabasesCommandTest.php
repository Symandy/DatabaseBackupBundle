<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Functional\Command;

use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\ToolsException;
use Symandy\Tests\DatabaseBackupBundle\Functional\AbstractFunctionalTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class BackupDatabasesCommandTest extends AbstractFunctionalTestCase
{

    /**
     * @throws ORMException
     * @throws DBALException
     * @throws ToolsException
     */
    protected function setUp(): void
    {
        $entityManager = $this->getEntityManager();

        $schemaManager = $entityManager->getConnection()->createSchemaManager();
        $options = $this->getConnectionOptions(true);

        if (in_array($options['dbname'], $schemaManager->listDatabases())) {
            $schemaManager->dropDatabase($options['dbname']);
        }

        $schemaManager->createDatabase($options['dbname']);

        $filesystem = new Filesystem();
        if (!$filesystem->exists([self::$kernel->getProjectDir() . '/backups'])) {
            $filesystem->mkdir([self::$kernel->getProjectDir() . '/backups']);
        }
    }

    public function testBackupCommand(): void
    {
        if (!self::$booted) {
            self::bootKernel();
        }

        $application = new Application(self::$kernel);
        $command = $application->find('symandy:databases:backup');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $backupFiles = (new Finder())
            ->in([self::$kernel->getProjectDir() . '/backups'])
            ->depth('== 0')
            ->name(['main-db_test_1-*.sql'])
            ->files()
            ->name('*.sql')
        ;

        self::assertEquals(1, $backupFiles->count());
    }

    public function testBackupCommandMaxFiles(): void
    {
        if (!self::$booted) {
            self::bootKernel();
        }

        $filesystem = new Filesystem();
        $filesystem->touch(
            [self::$kernel->getProjectDir() . '/backups/other-backup-db_test_1-2022-04-01.sql'],
            (new DateTime('2022-03-01'))->getTimestamp()
        );
        $filesystem->touch(
            [self::$kernel->getProjectDir() . '/backups/other-backup-main-db_test_1-2022-04-01.sql'],
            (new DateTime('2022-03-01'))->getTimestamp()
        );

        foreach (range(1, 10) as $day) {
            $date = new DateTime("2022-04-$day");
            $formattedDay = str_pad((string) $day, 2, '0', STR_PAD_LEFT);

            $filesystem->touch(
                [self::$kernel->getProjectDir() . "/backups/main-db_test_1-2022-04-$formattedDay.sql"],
                $date->getTimestamp()
            );
        }

        $application = new Application(self::$kernel);
        $command = $application->find('symandy:databases:backup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $backupFilesFinder = (new Finder())
            ->in([self::$kernel->getProjectDir() . '/backups'])
            ->depth('== 0')
            ->name(['main-db_test_1-*.sql'])
            ->files()
        ;
        $otherBackupFilesFinder = (new Finder())
            ->in([self::$kernel->getProjectDir() . '/backups'])
            ->depth('== 0')
            ->name(['other-backup-db_test_1-*.sql'])
            ->name(['other-backup-main-db_test_1-*.sql'])
            ->files()
        ;

        self::assertEquals(5, $backupFilesFinder->count());
        self::assertEquals(2, $otherBackupFilesFinder->count());
        $formattedTodayDate = (new DateTime())->format('Y-m-d');

        $backupFiles = iterator_to_array($backupFilesFinder);
        $otherBackupFiles = iterator_to_array($otherBackupFilesFinder);
        $filePathPrefix = self::$kernel->getProjectDir() . '/backups/main-db_test_1';

        self::assertArrayNotHasKey("$filePathPrefix-2022-04-01.sql", $backupFiles);
        self::assertArrayNotHasKey("$filePathPrefix-2022-04-02.sql", $backupFiles);
        self::assertArrayNotHasKey("$filePathPrefix-2022-04-03.sql", $backupFiles);
        self::assertArrayNotHasKey("$filePathPrefix-2022-04-04.sql", $backupFiles);
        self::assertArrayNotHasKey("$filePathPrefix-2022-04-05.sql", $backupFiles);
        self::assertArrayNotHasKey("$filePathPrefix-2022-04-06.sql", $backupFiles);
        self::assertArrayHasKey("$filePathPrefix-2022-04-07.sql", $backupFiles);
        self::assertArrayHasKey("$filePathPrefix-2022-04-08.sql", $backupFiles);
        self::assertArrayHasKey("$filePathPrefix-2022-04-09.sql", $backupFiles);
        self::assertArrayHasKey("$filePathPrefix-2022-04-10.sql", $backupFiles);
        self::assertArrayHasKey("$filePathPrefix-$formattedTodayDate.sql", $backupFiles);
        self::assertArrayHasKey(
            self::$kernel->getProjectDir() . '/backups/other-backup-db_test_1-2022-04-01.sql',
            $otherBackupFiles
        );
        self::assertArrayHasKey(
            self::$kernel->getProjectDir() . '/backups/other-backup-main-db_test_1-2022-04-01.sql',
            $otherBackupFiles
        );
    }

    /**
     * @throws DBALException
     * @throws ORMException
     */
    protected function tearDown(): void
    {
        $entityManager = $this->getEntityManager();

        $schemaManager = $entityManager->getConnection()->createSchemaManager();
        $options = $this->getConnectionOptions(true);

        if (in_array($options['dbname'], $schemaManager->listDatabases())) {
            $schemaManager->dropDatabase($options['dbname']);
        }

        $backupFiles = (new Finder())
            ->in([self::$kernel->getProjectDir() . '/backups'])
            ->depth('== 0')
            ->files()
            ->name('*.sql')
        ;

        (new Filesystem())->remove($backupFiles);
    }

    private function getConnectionOptions(bool $withDbName = false): array
    {
        $container = self::getContainer();

        $baseParams = [
            'driver' => 'pdo_mysql',
            'user' => $container->getParameter('test.database_user'),
            'password' => $container->getParameter('test.database_password'),
            'host' => $container->getParameter('test.database_host'),
            'port' => $container->getParameter('test.database_port')
        ];

        if (!$withDbName) {
            return $baseParams;
        }

        return [...$baseParams, 'dbname' => $container->getParameter('test.database_name')];
    }

    /**
     * @throws ORMException
     */
    private function getEntityManager(): EntityManagerInterface
    {
        $paths = [__DIR__ . '/../../app/src/Entity'];
        $connectionOptions = $this->getConnectionOptions();

        $config = Setup::createAttributeMetadataConfiguration($paths);

        return EntityManager::create($connectionOptions, $config);
    }

}
