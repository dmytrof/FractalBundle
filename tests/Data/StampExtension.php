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

namespace Dmytrof\FractalBundle\Tests\Data;

use Dmytrof\FractalBundle\Transformer\{AbstractTransformer, Extension\AbstractExtension};

/**
 * Adds data to every transformed subject
 */
class StampExtension extends AbstractExtension
{
    protected function _supports(\ReflectionClass $reflectionClass, AbstractTransformer $transformer): bool
    {
        return true;
    }

    public function transform($subject, AbstractTransformer $transformer): array
    {
        // Subject data has priority over extension data
        return ['stamp' => 'stamped', 'bar' => 'ignored'];
    }
}
