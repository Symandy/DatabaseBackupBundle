<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Command;

use DateTime;
use Symandy\DatabaseBackupBundle\Model\Backup\Backup;
use Symandy\DatabaseBackupBundle\Model\Connection\MySQLConnection;
use Symandy\DatabaseBackupBundle\Registry\BackupRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'symandy:databases:backup',
    description: "Dump and export databases from configuration"
)]
final class BackupDatabasesCommand extends Command
{

    public function __construct(private readonly BackupRegistry $backupRegistry)
    {
        parent::__construct();
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

        /** @var Backup $backup */
        foreach ($backupsToExecute as $backup) {
            /** @var MySQLConnection $connection */
            $connection = $backup->getConnection();

            $dumpSqlCommand = sprintf(
                '%s -u "${:DB_USER}" -h "${:DB_HOST}" -P "${:DB_PORT}" --databases %s > "${:FILENAME}".sql',
                $mysqldump,
                implode(' ', $connection->getDatabases()),
            );

            $io->info(sprintf("The backup %s is in progress", $backup->getName()));

            $process = Process::fromShellCommandline($dumpSqlCommand);
            $process->setPty(Process::isPtySupported());
            $process->run(null, [
                'DB_USER' => $connection->getUser(),
                'DB_HOST' => $connection->getHost(),
                'DB_PORT' => $connection->getPort(),
                'MYSQL_PWD' => $connection->getPassword(),
                'FILENAME' => sprintf('%s-%s', $backup->getName(), (new DateTime())->format('Y-m-d'))
            ]);

            if (!$process->isSuccessful()) {
                $io->error($process->getErrorOutput());

                return Command::FAILURE;
            }

            $io->success(sprintf('Backup %s has been successfully completed', $backup->getName()));
        }

        return Command::SUCCESS;
    }

}
