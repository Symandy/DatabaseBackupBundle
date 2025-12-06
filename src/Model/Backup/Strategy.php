<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model\Backup;

class Strategy
{
    /**
     * @param list<string>|null $tables
     */
    public function __construct(
        private readonly ?int $maxFiles = null,
        private readonly ?string $backupDirectory = null,
        private readonly ?string $dateFormat = 'Y-m-d',
        private readonly ?bool $onlyData = false,
        private readonly ?array $tables = null,
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

    public function getOnlyData(): ?bool
    {
        return $this->onlyData;
    }

    /** @return list<string>|null */
    public function getTables(): ?array
    {
        return $this->tables;
    }
}
