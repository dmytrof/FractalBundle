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

namespace Dmytrof\FractalBundle\Model;

use Dmytrof\FractalBundle\Transformer\AbstractTransformer;

interface FractalTransformableInterface
{
    /**
     * Returns fractal transformer class
     *
     * @return class-string<AbstractTransformer>
     */
    public static function getFractalTransformerClass(): string;
}
