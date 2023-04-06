<?php

declare(strict_types=1);

namespace Symandy\Tests\DatabaseBackupBundle\app\src\Model;

final class Foo
{
    public function __construct(
        public readonly string $fooBar,
        public readonly string $baz,
    ) {
    }
}
