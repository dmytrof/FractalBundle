<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dmytrof\FractalBundle\Tests\Serializer;

use Dmytrof\FractalBundle\Serializer\SimpleArraySerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SimpleArraySerializerTest extends TestCase
{
    #[DataProvider('collectionDataProvider')]
    public function testSerializeCollection(?string $resourceKey, array $data, array $result): void
    {
        $serializer = new SimpleArraySerializer();

        $this->assertSame($result, $serializer->collection($resourceKey, $data));
    }

    public static function collectionDataProvider(): iterable
    {
        yield 'resource key' => ['test', ['foo' => 'bar'], ['test' => ['foo' => 'bar']]];
        yield 'numeric resource key' => ['1', ['foo' => 'bar'], [1 => ['foo' => 'bar']]];
        yield 'empty resource key' => ['', ['foo' => 'bar'], ['foo' => 'bar']];
        yield 'zero resource key' => ['0', ['foo' => 'bar'], ['foo' => 'bar']];
        yield 'null resource key' => [null, ['foo' => 'bar'], ['foo' => 'bar']];
    }

    public function testSerializeItem(): void
    {
        $serializer = new SimpleArraySerializer();

        $this->assertSame(['foo' => 'bar'], $serializer->item('test', ['foo' => 'bar']));
        $this->assertSame(['foo' => 'bar'], $serializer->item(null, ['foo' => 'bar']));
        $this->assertSame([], $serializer->item(null, []));
        $this->assertNull($serializer->null());
    }
}
