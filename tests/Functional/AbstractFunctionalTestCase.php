<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Functional;

use Symandy\Tests\DatabaseBackupBundle\app\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractFunctionalTestCase extends WebTestCase
{

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

}
