<?php

namespace Symandy\Tests\DatabaseBackupBundle\Unit\Builder;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symandy\DatabaseBackupBundle\Builder\ConfigurationBuilder;
use Symandy\DatabaseBackupBundle\Model\Connection\ConnectionDriver;

final class ConfigurationBuilderTest extends TestCase
{
    /**
     * @dataProvider provideUrls
     */
    public function testBuildFromUrl(array $expected, string $url): void
    {
        self::assertSame($expected, ConfigurationBuilder::buildFromUrl($url));
    }

    public function testNullDriver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not parse the scheme from the url');

        ConfigurationBuilder::buildFromUrl('host:9999');
    }

    public function testInvalidDriver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver test is not supported');

        ConfigurationBuilder::buildFromUrl('test://user@host');
    }

    public function testNullPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database could not be parsed');

        ConfigurationBuilder::buildFromUrl('mysql://user@host');
    }

    public function testInvalidPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database could not be parsed');

        ConfigurationBuilder::buildFromUrl('mysql://user@host/');
    }

    /**
     * @return iterable<string, array{expected: array, url: string}>
     */
    private static function provideUrls(): iterable
    {
        yield 'mysql_1' => [
            'expected' => [
                'driver' => ConnectionDriver::MySQL,
                'configuration' => [
                    'user' => 'user',
                    'password' => 'password',
                    'databases' => ['db_name'],
                    'host' => 'host',
                    'port' => 9999,
                ]
            ],
            'url' => 'mysql://user:password@host:9999/db_name?additionalParameter=test'
        ];

        yield 'mysql_2_null_values' => [
            'expected' => [
                'driver' => ConnectionDriver::MySQL,
                'configuration' => [
                    'user' => null,
                    'password' => null,
                    'databases' => ['db_name']
                ]
            ],
            'url' => 'mysql:///db_name'
        ];
    }
}
