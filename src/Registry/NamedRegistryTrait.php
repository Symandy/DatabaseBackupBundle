<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Registry;

use InvalidArgumentException;

use function array_key_exists;
use function sprintf;

/**
 * @template T of object
 */
trait NamedRegistryTrait
{
    /** @var array<string, T> */
    private array $registry = [];

    /**
     * @return array<string, T>
     */
    public function all(): array
    {
        return $this->registry;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registry);
    }

    /**
     * @return T
     */
    public function get(string $name): object
    {
        return $this->registry[$name] ?? throw new InvalidArgumentException(sprintf(
            'class %s does not have any item named %s in its registry',
            static::class,
            $name
        ));
    }

    /**
     * @param T $item
     */
    public function register(string $name, object $item): void
    {
        $this->registry[$name] = $item;
    }
}
