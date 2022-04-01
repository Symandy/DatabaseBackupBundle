<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Command;

use DateTime;
use Symandy\DatabaseBackupBundle\Model\MySQLConnection;
use Symandy\DatabaseBackupBundle\Registry\ConnectionRegistryInterface;
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

    public function __construct(private ConnectionRegistryInterface $connectionRegistry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'connections',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'The configured database connections to export'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var array<int, string> $connections */
        $connections = $input->getArgument('connections');
        $connectionsToExport = [];

        foreach ($connections as $connection) {
            if (!$this->connectionRegistry->has($connection)) {
                $io->error("Connection $connection does not exist");

                return Command::FAILURE;
            }

            $connectionsToExport[] = $this->connectionRegistry->get($connection);
        }

        if ([] === $connections) {
            $connectionsToExport = $this->connectionRegistry->all();
        }

        $mysqldump = (new ExecutableFinder())->find('mysqldump');

        foreach ($connectionsToExport as $connection) {
            /** @var MySQLConnection $connection */

            $dumpSqlCommand = sprintf(
                "MYSQL_PWD=%s $mysqldump -u %s extranet > %s-%s.sql",
                $connection->getPassword(),
                $connection->getUser(),
                $connection->getName(),
                (new DateTime())->format('Y-m-d')
            );

            $io->info(sprintf("Start exporting databases for %s connection", $connection->getName()));

            $process = Process::fromShellCommandline($dumpSqlCommand);
            $process->run(fn () => $io->success('Dump succeed'));
        }

        return Command::SUCCESS;
    }

}
