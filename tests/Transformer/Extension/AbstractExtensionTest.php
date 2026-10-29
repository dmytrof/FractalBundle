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

namespace Dmytrof\FractalBundle\Tests\Transformer\Extension;

use Dmytrof\FractalBundle\Service\ExtensionsContainer;
use Dmytrof\FractalBundle\Tests\Data\{Foo, FooTransformer, TestDataExtension, TestExtension};
use PHPUnit\Framework\TestCase;

class AbstractExtensionTest extends TestCase
{
    private FooTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new FooTransformer(new ExtensionsContainer());
    }

    public function testSupports(): void
    {
        $extension = new TestDataExtension();

        $this->assertTrue($extension->supports(new \ReflectionClass(Foo::class), $this->transformer));
        $this->assertFalse($extension->supports(new \ReflectionClass(\stdClass::class), $this->transformer));
    }

    public function testSupportsIsCachedBySubjectClass(): void
    {
        $extension = new TestExtension();

        $this->assertFalse($extension->supports(new \ReflectionClass(Foo::class), $this->transformer));
        $this->assertFalse($extension->supports(new \ReflectionClass(Foo::class), $this->transformer));
        $this->assertSame(1, $extension->supportsCalls);

        $extension->supports(new \ReflectionClass(\stdClass::class), $this->transformer);
        $this->assertSame(2, $extension->supportsCalls);
    }

    public function testDefaults(): void
    {
        $extension = new TestExtension();

        $this->assertSame([], $extension->transform(new Foo(), $this->transformer));

        $extension->decorateTransformer($this->transformer);
        $this->assertSame(['boo', 'child'], $this->transformer->getAvailableIncludes());
        $this->assertSame([], $this->transformer->getDefaultIncludes());
    }
}
