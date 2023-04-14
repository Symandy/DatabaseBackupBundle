<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model\Backup;

class Strategy
{
    public function __construct(
        private readonly ?int $maxFiles = null,
        private readonly ?string $backupDirectory = null,
        private readonly ?string $dateFormat = 'Y-m-d'
    ) {
    }

    public function getMaxFiles(): ?int
    {
        return $this->maxFiles;
    }

    public function getBackupDirectory(): ?string
    {
        return $this->backupDirectory;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }
}
