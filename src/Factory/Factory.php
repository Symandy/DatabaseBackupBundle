<?php

declare(strict_types=1);

namespace Symandy\DatabaseBackupBundle\Factory;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @template T of object
 * @implements FactoryInterface<T>
 */
class Factory implements FactoryInterface
{

    private readonly ObjectNormalizer $normalizer;

    /**
     * @param class-string<T> $className
     */
    public function __construct(protected readonly string $className)
    {
        $this->normalizer = new ObjectNormalizer(nameConverter: new CamelCaseToSnakeCaseNameConverter());
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(array $options): object
    {
        /** @var T $object */
        $object = $this->normalizer->denormalize($options, $this->className);

        return $object;
    }

}
