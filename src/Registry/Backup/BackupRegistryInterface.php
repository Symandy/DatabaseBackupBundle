<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry\Backup;

use Symandy\DatabaseBackupBundle\Model\Backup\Backup;
use Symandy\DatabaseBackupBundle\Registry\NamedRegistry;

/**
 * @extends NamedRegistry<Backup>
 */
interface BackupRegistryInterface extends NamedRegistry
{

    public function registerFromNameAndOptions(string $name, array $options): void;

}
