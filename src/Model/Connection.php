<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model;

interface Connection
{

    public function getName(): ?string;

    /**
     * @return array<string, string|int|float>
     */
    public function getOptions(): array;

}
