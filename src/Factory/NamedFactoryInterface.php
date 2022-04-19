<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory;

/**
 * @template T of object
 */
interface NamedFactoryInterface
{

    /**
     * @return T
     */
    public function createNamed(string $name, array $options): object;

}
