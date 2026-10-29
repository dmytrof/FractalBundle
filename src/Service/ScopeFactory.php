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

namespace Dmytrof\FractalBundle\Service;

use Dmytrof\FractalBundle\Exception\RuntimeException;
use Dmytrof\FractalBundle\Scope\Scope;
use League\Fractal\{Manager, Resource\ResourceInterface, ScopeFactory as BaseScopeFactory};

class ScopeFactory extends BaseScopeFactory
{
    public function createScopeFor(Manager $manager, ResourceInterface $resource, ?string $scopeIdentifier = null): Scope
    {
        if (!$manager instanceof FractalManager) {
            throw new RuntimeException(sprintf('Manager must be instance of %s. Input was: %s', FractalManager::class, get_debug_type($manager)));
        }

        return new Scope($manager, $resource, $scopeIdentifier);
    }
}
