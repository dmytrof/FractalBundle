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

use Dmytrof\FractalBundle\Service\ExtensionsContainer;
use Dmytrof\FractalBundle\Tests\Data\{TestDataExtension, TestExtension};
use PHPUnit\Framework\TestCase;

class ExtensionsContainerTest extends TestCase
{
    public function testCreateContainer(): void
    {
        $container = new ExtensionsContainer([]);
        $this->assertCount(0, $container);
        $this->assertInstanceOf(\Iterator::class, $container->getIterator());

        $extension = new TestDataExtension();
        $container2 = new ExtensionsContainer((static fn () => yield $extension)());
        $this->assertCount(1, $container2);
        $this->assertSame([$extension], iterator_to_array($container2));
    }

    public function testAddExtension(): void
    {
        $container = new ExtensionsContainer();
        $extension1 = new TestDataExtension();
        $extension2 = new TestExtension();

        $this->assertSame($container, $container->addExtension($extension1)->addExtension($extension2));
        $this->assertCount(2, $container);
        $this->assertSame([$extension1, $extension2], iterator_to_array($container));
    }

    public function testAddInvalidExtension(): void
    {
        $this->expectException(\TypeError::class);

        new ExtensionsContainer([new \stdClass()]);
    }
}
