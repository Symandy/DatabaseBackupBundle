<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\app;

use Exception;
use Symandy\DatabaseBackupBundle\SymandyDatabaseBackupBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SymandyDatabaseBackupBundle(),
        ];
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/packages/config.yaml');
        $loader->load($this->getProjectDir() . '/config/services.yaml');
    }
}
