<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Command;

use DateTime;
use RuntimeException;
use Symandy\DatabaseBackupBundle\Model\Backup\Backup;
use Symandy\DatabaseBackupBundle\Model\Connection\MySQLConnection;
use Symandy\DatabaseBackupBundle\Registry\Backup\BackupRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

use function array_splice;
use function getcwd;
use function iterator_to_array;
use function sprintf;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'symandy:databases:backup',
    description: 'Dump and export databases from configuration'
)]
final class BackupDatabasesCommand extends Command
{
    private Filesystem $filesystem;

    public function __construct(private readonly BackupRegistry $backupRegistry)
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'backups',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'The name of the backups to be performed'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var array<int, string> $backups */
        $backups = $input->getArgument('backups');
        $backupsToExecute = [];

        foreach ($backups as $backup) {
            if (!$this->backupRegistry->has($backup)) {
                $io->error("Backup $backup does not exist in registry");

                return Command::FAILURE;
            }

            $backupsToExecute[] = $this->backupRegistry->get($backup);
        }

        if ([] === $backups) {
            $backupsToExecute = $this->backupRegistry->all();
        }

        if ([] === $backupsToExecute) {
            $io->warning('No backup to be done');

            return Command::SUCCESS;
        }

        $mysqldump = (new ExecutableFinder())->find('mysqldump');

        if (null === $mysqldump) {
            $io->error('Cannot find "mysqldump" executable');

            return Command::INVALID;
        }

        /** @var Backup $backup */
        foreach ($backupsToExecute as $backup) {
            /** @var MySQLConnection $connection */
            $connection = $backup->getConnection();
            $backupName = $backup->getName();

            if ([] === $connection->getDatabases()) {
                $io->warning(sprintf('No database to backup in %s configuration, Skipping', $backupName));

                continue;
            }

            if (null !== ($backupDirectory = $backup->getStrategy()->getBackupDirectory())) {
                if (!$this->filesystem->exists($backupDirectory)) {
                    $io->error("Backup directory \"$backupDirectory\" does not exist");

                    return Command::INVALID;
                }
            }

            $backupDirectory ??= false !== getcwd() ?
                getcwd() :
                throw new RuntimeException('Unable to get the current directory, check the user permissions')
            ;

            $io->info(sprintf('The backup %s is in progress', $backupName));

            foreach ($connection->getDatabases() as $database) {
                if ($output->isVerbose()) {
                    $io->comment("Backup for $database database has started");
                }

                $date = (new DateTime())->format($backup->getStrategy()->getDateFormat() ?: 'Y-m-d');
                $filePath = "$backupDirectory/$backupName-$database-$date.sql";

                $process = Process::fromShellCommandline(
                    '"${:MYSQL_DUMP}" -u "${:DB_USER}" -h "${:DB_HOST}" -P "${:DB_PORT}" "${:DB_NAME}" > "${:FILEPATH}"'
                );

                $process->setPty(Process::isPtySupported());
                $process->run(null, [
                    'MYSQL_DUMP' => $mysqldump,
                    'DB_USER' => $connection->getUser(),
                    'DB_HOST' => $connection->getHost(),
                    'DB_PORT' => $connection->getPort(),
                    'DB_NAME' => $database,
                    'MYSQL_PWD' => $connection->getPassword(),
                    'FILEPATH' => $filePath,
                ]);

                if (!$process->isSuccessful()) {
                    $message = '' !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

                    $io->error(u($message)->collapseWhitespace()->toString());

                    return Command::FAILURE;
                }

                $finder = (new Finder())
                    ->in($backupDirectory)
                    ->name(["$backupName-$database-*.sql"])
                    ->sortByModifiedTime()
                    ->depth(['== 0'])
                    ->files()
                ;

                $filesCount = $finder->count();

                /** @var array<int, SplFileInfo> $files */
                $files = iterator_to_array($finder);

                $maxFiles = $backup->getStrategy()->getMaxFiles();

                if (null !== $maxFiles && $filesCount > $maxFiles) {
                    $filesToDeleteCount = $filesCount - $maxFiles;
                    array_splice($files, $filesToDeleteCount);

                    if (1 === $filesToDeleteCount) {
                        $io->warning('Reached the max backup files limit, removing the oldest one');
                    } else {
                        $io->warning(sprintf(
                            'Reached the max backup files limit, removing the %d oldest ones',
                            $filesToDeleteCount
                        ));
                    }

                    foreach ($files as $file) {
                        if ($output->isVerbose()) {
                            $io->comment(sprintf('Deleting "%s"', $file->getRealPath()));
                        }

                        $this->filesystem->remove($file->getRealPath());
                    }
                }
            }

            $io->success(sprintf('Backup %s has been successfully completed', $backupName));
        }

        return Command::SUCCESS;
    }
}
