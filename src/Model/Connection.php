<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Model;

interface Connection
{

    public function getOptions(): array;

}
