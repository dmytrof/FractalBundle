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

namespace Dmytrof\FractalBundle\Tests\Service;

use Dmytrof\FractalBundle\Exception\TransformerException;
use Dmytrof\FractalBundle\Serializer\SimpleArraySerializer;
use Dmytrof\FractalBundle\Service\{ExtensionsContainer, FractalManager, ScopeFactory, TransformersContainer};
use Dmytrof\FractalBundle\Tests\Data\{Foo, FooTransformer, TestDataExtension};
use PHPUnit\Framework\TestCase;

class FractalManagerTest extends TestCase
{
    private FractalManager $fractalManager;

    protected function setUp(): void
    {
        $extensionContainer = new ExtensionsContainer([new TestDataExtension()]);
        $this->fractalManager = new FractalManager(new TransformersContainer([new FooTransformer($extensionContainer)]), new ScopeFactory());
    }

    private function foo(): Foo
    {
        $foo = new Foo();
        $foo->bar = 'baz';
        $foo->baz = 'bar';
        $foo->gaz = 'bass';

        return $foo;
    }

    public function testDefaults(): void
    {
        $manager = new FractalManager(new TransformersContainer());

        $this->assertInstanceOf(SimpleArraySerializer::class, $manager->getSerializer());
        $this->assertSame('fractal', FractalManager::CODE);
    }

    public function testUsage(): void
    {
        $foo = $this->foo();

        $this->assertEquals([
            'bar' => 'baz',
            'baz' => 'bar',
            TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA,
        ], $this->fractalManager->getModelData($foo, FooTransformer::class));

        $transformer = $this->fractalManager->getTransformer(FooTransformer::class)->removeDefaultInclude(TestDataExtension::TEST_DATA_INCLUDE);
        $this->assertEquals([
            'bar' => 'baz',
            'baz' => 'bar',
        ], $this->fractalManager->getModelData($foo, $transformer));

        $this->assertEquals([
            'bar' => 'baz',
            'baz' => 'bar',
            'boo' => Foo::BOO_DATA,
        ] + Foo::TEST_DATA, (clone $this->fractalManager)->parseIncludes([TestDataExtension::TEST_DATA_INCLUDE, 'boo'])->getModelData($foo, $transformer->addIncludeToRoot(TestDataExtension::TEST_DATA_INCLUDE)));

        $this->assertEquals([
            'bar' => 'baz',
            'baz' => 'bar',
            'boo' => Foo::BOO_DATA,
        ] + Foo::TEST_DATA, (clone $this->fractalManager)->parseIncludes([TestDataExtension::TEST_DATA_INCLUDE])->getModelData($foo, $transformer->addDefaultInclude('boo')));
    }

    public function testModelDataOfNull(): void
    {
        $this->assertNull($this->fractalManager->getModelData(null, FooTransformer::class));
    }

    public function testCollectionAndPaginatorData(): void
    {
        $expected = [
            ['bar' => 'baz', 'baz' => 'bar', TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA],
            ['bar' => 'baz', 'baz' => 'bar', TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA],
        ];

        $this->assertSame($expected, $this->fractalManager->getCollectionData([$this->foo(), $this->foo()], FooTransformer::class));
        $this->assertSame($expected, $this->fractalManager->getPaginatorData(new \ArrayIterator([$this->foo(), $this->foo()]), FooTransformer::class));
        $this->assertSame([], $this->fractalManager->getCollectionData([], FooTransformer::class));
    }

    public function testCallableTransformer(): void
    {
        $this->assertSame(['a' => 1, 'b' => 2], $this->fractalManager->getModelData(['b' => 2, 'a' => 1], static fn (array $data): array => $data));
    }

    public function testGetTransformersForSubject(): void
    {
        $transformers = $this->fractalManager->getTransformersForSubject($this->foo());

        $this->assertCount(1, $transformers);
        $this->assertInstanceOf(FooTransformer::class, $transformers->first());
    }

    public function testGetTransformersForUnsupportedSubject(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('There is no transformer for subject stdClass');

        $this->fractalManager->getTransformersForSubject(new \stdClass());
    }

    public function testGetTransformersForScalarSubject(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('There is no transformer for subject foo');

        $this->fractalManager->getTransformersForSubject('foo');
    }

    public function testGetUndefinedTransformer(): void
    {
        $this->expectException(TransformerException::class);

        $this->fractalManager->getTransformer('App\\UnknownTransformer');
    }
}
