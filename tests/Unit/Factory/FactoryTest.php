<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\Factory\Factory;
use Symandy\Tests\DatabaseBackupBundle\app\src\Model\Foo;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;

final class FactoryTest extends TestCase
{

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testCreateValidOptions(): void
    {
        $options = ['foo_bar' => 'test-1', 'baz' => 'test-2'];
        $fooFactory = new Factory(Foo::class);

        $foo = $fooFactory->create($options);

        self::assertInstanceOf(Foo::class, $foo);
        self::assertEquals('test-1', $foo->fooBar);
        self::assertEquals('test-2', $foo->baz);
    }

    public function testCreateMissingOptions(): void
    {
        $options = ['boo' => 'test'];

        $fooFactory = new Factory(Foo::class);

        $this->expectException(SerializerException::class);

        $fooFactory->create($options);
    }

}
