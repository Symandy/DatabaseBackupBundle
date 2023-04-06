<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle;

use Symandy\DatabaseBackupBundle\DependencyInjection\Compiler\RegisterConnectionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SymandyDatabaseBackupBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterConnectionsPass());
    }
}
