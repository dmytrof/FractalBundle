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

namespace Dmytrof\FractalBundle\Tests\Scope;

use Dmytrof\FractalBundle\Exception\TransformerException;
use Dmytrof\FractalBundle\Scope\Scope;
use Dmytrof\FractalBundle\Service\{ExtensionsContainer, FractalManager, TransformersContainer};
use Dmytrof\FractalBundle\Tests\Data\{Foo, FooTransformer};
use League\Fractal\Resource\{Collection, Item, ResourceInterface};
use League\Fractal\Serializer\ArraySerializer;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    private FractalManager $manager;

    protected function setUp(): void
    {
        $this->manager = new FractalManager(new TransformersContainer([new FooTransformer(new ExtensionsContainer())]));
    }

    private function foo(string $bar, string $baz = 'baz'): Foo
    {
        $foo = new Foo();
        $foo->bar = $bar;
        $foo->baz = $baz;

        return $foo;
    }

    public function testCreateScope(): void
    {
        $resource = $this->createStub(ResourceInterface::class);

        $scope = new Scope($this->manager, $resource, 'test');

        $this->assertSame($this->manager, $scope->getManager());
        $this->assertSame($resource, $scope->getResource());
        $this->assertSame('test', $scope->getScopeIdentifier());
    }

    public function testTransformerClassName(): void
    {
        $scope = $this->manager->createData(new Item($this->foo('b'), FooTransformer::class));

        $this->assertInstanceOf(Scope::class, $scope);
        $this->assertSame(['bar' => 'b', 'baz' => 'baz'], $scope->toArray());
    }

    public function testUndefinedTransformerClassName(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Undefined transformer with class App\\UnknownTransformer');

        $this->manager->createData(new Item($this->foo('b'), 'App\\UnknownTransformer'))->toArray();
    }

    public function testDataIsSortedByKeys(): void
    {
        $scope = $this->manager->createData(new Item(['b' => 2, 'a' => 1, 'c' => 3], fn (array $data) => $data));

        $this->assertSame(['a', 'b', 'c'], array_keys($scope->toArray()));
    }

    public function testNullItemIsSerializedAsNull(): void
    {
        $this->assertNull($this->manager->createData(new Item(null, FooTransformer::class))->toArray());
    }

    public function testNullItemWithAnotherSerializer(): void
    {
        $this->manager->setSerializer(new ArraySerializer());

        $this->assertSame([], $this->manager->createData(new Item(null, FooTransformer::class))->toArray());
    }

    public function testNullIncludedItem(): void
    {
        $this->manager->parseIncludes('child');

        $this->assertSame(['bar' => 'b', 'baz' => 'baz', 'child' => null], $this->manager->createData(new Item($this->foo('b'), FooTransformer::class))->toArray());
    }

    public function testIncludedItemByTransformerClassName(): void
    {
        $foo = $this->foo('parent');
        $foo->child = $this->foo('child');
        $this->manager->parseIncludes('child');

        $this->assertSame([
            'bar' => 'parent',
            'baz' => 'baz',
            // Nested "child.child" include is not requested
            'child' => ['bar' => 'child', 'baz' => 'baz'],
        ], $this->manager->createData(new Item($foo, FooTransformer::class))->toArray());
    }

    public function testCollectionWithResourceKey(): void
    {
        $data = $this->manager->createData(new Collection([$this->foo('1'), $this->foo('2')], FooTransformer::class, 'items'))->toArray();

        $this->assertSame(['items' => [['bar' => '1', 'baz' => 'baz'], ['bar' => '2', 'baz' => 'baz']]], $data);
    }
}
