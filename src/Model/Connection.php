<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model;

interface Connection
{

    /**
     * @return array<string, string|int|float>
     */
    public function getOptions(): array;

}
