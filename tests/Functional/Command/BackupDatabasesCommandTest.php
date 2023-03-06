<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Functional\Command;

use DateTime;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\ORM\ORMSetup;
use Symandy\Tests\DatabaseBackupBundle\Functional\AbstractFunctionalTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Webmozart\Assert\Assert;

use function iterator_to_array;

final class BackupDatabasesCommandTest extends AbstractFunctionalTestCase
{
    /**
     * @throws DBALException
     * @throws MissingMappingDriverImplementation
     */
    protected function setUp(): void
    {
        $this->restoreDatabaseWithParams();
        $this->restoreDatabaseWithUrl();

        $filesystem = new Filesystem();
        if (!$filesystem->exists([self::$kernel->getProjectDir() . '/backups'])) {
            $filesystem->mkdir([self::$kernel->getProjectDir() . '/backups']);
        }
    }

    /**
     * @throws DBALException
     * @throws MissingMappingDriverImplementation
     */
    private function restoreDatabaseWithParams(): void
    {
        $entityManager = $this->getEntityManager();
        $schemaManager = $entityManager->getConnection()->createSchemaManager();

        $options = $this->getConnectionOptions(withDbName: true);

        if (in_array($options['dbname'], $schemaManager->listDatabases())) {
            $schemaManager->dropDatabase($options['dbname']);
        }

        $schemaManager->createDatabase($options['dbname']);
    }

    /**
     * @throws DBALException
     * @throws MissingMappingDriverImplementation
     */
    private function restoreDatabaseWithUrl(): void
    {
        $entityManager = $this->getEntityManager();
        $schemaManager = $entityManager->getConnection()->createSchemaManager();

        $options = $this->getConnectionOptions(withUrl: true, withDbName: true);

        if (in_array($options['dbname'], $schemaManager->listDatabases())) {
            $schemaManager->dropDatabase($options['dbname']);
        }

        $schemaManager->createDatabase($options['dbname']);
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
            (new DateTime('2022-04-01'))->getTimestamp(),
        );
        $filesystem->touch(
            [self::$kernel->getProjectDir() . '/backups/other-backup-main-db_test_1-2022-04-01.sql'],
            (new DateTime('2022-04-01'))->getTimestamp(),
        );
        $filesystem->touch(
            [self::$kernel->getProjectDir() . '/backups/secondary-db_test_2-2023-01-01.sql'],
            (new DateTime('2023-01-01'))->getTimestamp(),
        );
        $filesystem->touch(
            [self::$kernel->getProjectDir() . '/backups/secondary-db_test_2-2023-01-02.sql'],
            (new DateTime('2023-01-02'))->getTimestamp(),
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

        // Secondary backup is configured with full database url
        $secondaryBackupFilesFinder = (new Finder())
            ->in([self::$kernel->getProjectDir() . '/backups'])
            ->depth('== 0')
            ->name(['secondary-db_test_2-*.sql'])
        ;
        $secondaryBackupFiles = iterator_to_array($secondaryBackupFilesFinder);
        $filePathPrefix = self::$kernel->getProjectDir() . '/backups/secondary-db_test_2';

        self::assertCount(2, $secondaryBackupFilesFinder); // Backup strategy is configured with `max_files: 2`
        self::assertArrayNotHasKey("$filePathPrefix-2023-01-01.sql", $secondaryBackupFiles);
        self::assertArrayHasKey("$filePathPrefix-2023-01-02.sql", $secondaryBackupFiles);
        self::assertArrayHasKey("$filePathPrefix-$formattedTodayDate.sql", $secondaryBackupFiles);
    }

    /**
     * @throws DBALException
     * @throws MissingMappingDriverImplementation
     */
    protected function tearDown(): void
    {
        $this->restoreDatabaseWithParams();
        $this->restoreDatabaseWithUrl();

        $backupFiles = (new Finder())
            ->in([self::$kernel->getProjectDir() . '/backups'])
            ->depth('== 0')
            ->files()
            ->name('*.sql')
        ;

        (new Filesystem())->remove($backupFiles);
    }

    private function getConnectionOptions(bool $withUrl = false, bool $withDbName = false): array
    {
        $container = self::getContainer();

        if ($withUrl) {
            $databaseUrl = $container->getParameter('test.database_url');
            Assert::string($databaseUrl);

            $dsnParser = new DsnParser(['mysql' => 'mysqli']);

            return $dsnParser->parse($databaseUrl);
        }

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
     * @throws DBALException
     * @throws MissingMappingDriverImplementation
     */
    private function getEntityManager(): EntityManagerInterface
    {
        $paths = [__DIR__ . '/../../app/src/Entity'];
        $connectionOptions = $this->getConnectionOptions();

        $config = ORMSetup::createAttributeMetadataConfiguration($paths);

        return new EntityManager(DriverManager::getConnection($connectionOptions), $config);
    }
}
