<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Functional\Command;

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

        self::assertEquals(1, (new Finder())->in(['./'])->files()->name('*.sql')->count());
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

        foreach ((new Finder())->in(['./'])->files()->name('*.sql') as $file) {
            (new Filesystem())->remove([$file->getRealPath()]);
        }
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
