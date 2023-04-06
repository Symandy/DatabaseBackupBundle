<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory\Backup;

use Symandy\DatabaseBackupBundle\Factory\Connection\ConnectionFactory;
use Symandy\DatabaseBackupBundle\Factory\FactoryInterface;
use Symandy\DatabaseBackupBundle\Factory\NamedFactoryInterface;
use Symandy\DatabaseBackupBundle\Model\Backup\Backup;
use Symandy\DatabaseBackupBundle\Model\Backup\Strategy;

/**
 * @implements NamedFactoryInterface<Backup>
 */
final class BackupFactory implements NamedFactoryInterface
{
    /**
     * @param FactoryInterface<Strategy> $strategyFactory
     */
    public function __construct(
        private readonly ConnectionFactory $connectionFactory,
        private readonly FactoryInterface $strategyFactory
    ) {
    }

    public function createNamed(string $name, array $options): Backup
    {
        return new Backup(
            $name,
            $this->connectionFactory->create($options['connection']),
            $this->strategyFactory->create($options['strategy'])
        );
    }
}
