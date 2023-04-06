<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry;

/**
 * @template T of object
 */
interface NamedRegistry
{
    /**
     * @return array<string, T>
     */
    public function all(): array;

    public function has(string $name): bool;

    /**
     * @return T
     */
    public function get(string $name): object;

    /**
     * @param T $item
     */
    public function register(string $name, object $item): void;
}
