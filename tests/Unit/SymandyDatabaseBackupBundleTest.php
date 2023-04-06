<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Unit;

use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\DependencyInjection\Compiler\RegisterConnectionsPass;
use Symandy\DatabaseBackupBundle\SymandyDatabaseBackupBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SymandyDatabaseBackupBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $containerBuilder
            ->expects(self::once())->method('addCompilerPass')
            ->with(self::isInstanceOf(RegisterConnectionsPass::class))
        ;

        $bundle = new SymandyDatabaseBackupBundle();
        $bundle->build($containerBuilder);
    }
}
