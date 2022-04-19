<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry;

use Symandy\DatabaseBackupBundle\Factory\Backup\BackupFactory;
use Symandy\DatabaseBackupBundle\Model\Backup\Backup;

/**
 * @implements NamedRegistry<Backup>
 */
final class BackupRegistry implements NamedRegistry
{

    /** @use NamedRegistryTrait<Backup> */
    use NamedRegistryTrait;

    public function __construct(private readonly BackupFactory $backupFactory)
    {
    }

    public function registerFromNameAndOptions(string $name, array $options): void
    {
        $this->register($name, $this->backupFactory->createNamed($name, $options));
    }

}
