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

use Dmytrof\FractalBundle\Exception\RuntimeException;
use Dmytrof\FractalBundle\Scope\Scope;
use Dmytrof\FractalBundle\Service\{FractalManager, ScopeFactory, TransformersContainer};
use League\Fractal\Manager;
use League\Fractal\Resource\{Item, ResourceInterface};
use PHPUnit\Framework\TestCase;

class ScopeFactoryTest extends TestCase
{
    public function testCreateScope(): void
    {
        $scopeFactory = new ScopeFactory();
        $manager = new FractalManager(new TransformersContainer(), $scopeFactory);
        $resource = $this->createStub(ResourceInterface::class);

        $scope = $scopeFactory->createScopeFor($manager, $resource, 'foo');
        $this->assertInstanceOf(Scope::class, $scope);
        $this->assertSame('foo', $scope->getScopeIdentifier());

        $childScope = $scopeFactory->createChildScopeFor($manager, $scope, new Item(), 'bar');
        $this->assertInstanceOf(Scope::class, $childScope);
        $this->assertSame(['foo'], $childScope->getParentScopes());
    }

    public function testCreateScopeForAnotherManager(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Manager must be instance of '.FractalManager::class.'. Input was: '.Manager::class);

        (new ScopeFactory())->createScopeFor(new Manager(), $this->createStub(ResourceInterface::class));
    }
}
