<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory;

/**
 * @template T of object
 */
interface FactoryInterface
{

    /**
     * @return T
     */
    public function create(array $options): object;

}
