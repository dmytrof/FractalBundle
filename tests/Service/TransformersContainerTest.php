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
use Dmytrof\FractalBundle\Service\{ExtensionsContainer, TransformersContainer};
use Dmytrof\FractalBundle\Tests\Data\FooTransformer;
use PHPUnit\Framework\TestCase;

class TransformersContainerTest extends TestCase
{
    private FooTransformer $testTransformer;

    protected function setUp(): void
    {
        $this->testTransformer = new FooTransformer(new ExtensionsContainer());
    }

    public function testCreateContainer(): void
    {
        $container = new TransformersContainer([]);
        $this->assertCount(0, $container);
        $this->assertInstanceOf(\Iterator::class, $container->getIterator());

        $container2 = new TransformersContainer([$this->testTransformer]);
        $this->assertCount(1, $container2);
        $this->assertSame([FooTransformer::class => $this->testTransformer], iterator_to_array($container2));
    }

    public function testAddTransformer(): void
    {
        $container = new TransformersContainer();
        $this->assertSame($container, $container->addTransformer($this->testTransformer));

        // Transformers are unique by class
        $container->addTransformer($another = new FooTransformer(new ExtensionsContainer()));
        $this->assertCount(1, $container);

        $this->assertTrue($container->has(FooTransformer::class));
        $this->assertFalse($container->has('SomeClass'));
        $this->assertSame($another, $container->get(FooTransformer::class));
    }

    public function testAddInvalidTransformer(): void
    {
        $this->expectException(\TypeError::class);

        (new TransformersContainer())->addTransformer(new \stdClass());
    }

    public function testGetUndefinedTransformer(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Undefined transformer with class SomeClass');

        (new TransformersContainer([$this->testTransformer]))->get('SomeClass');
    }
}
