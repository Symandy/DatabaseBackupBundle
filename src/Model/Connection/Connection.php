<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model\Connection;

interface Connection
{
    /**
     * @return array<string, string|int>
     */
    public function getOptions(): array;
}
