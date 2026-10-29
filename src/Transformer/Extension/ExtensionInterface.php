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

namespace Dmytrof\FractalBundle\Transformer\Extension;

use Dmytrof\FractalBundle\Transformer\AbstractTransformer;

/**
 * Transformer extensions are services autoconfigured with "dmytrof.fractal.transformer.extension" tag
 */
interface ExtensionInterface
{
    /**
     * Checks if extension supports subjects of the class handled by the transformer
     *
     * @param \ReflectionClass<object> $reflection
     */
    public function supports(\ReflectionClass $reflection, AbstractTransformer $transformer): bool;

    /**
     * Decorates transformer (adds includes, include calls, etc.)
     */
    public function decorateTransformer(AbstractTransformer $transformer): void;

    /**
     * Returns data merged to the data of transformed subject
     */
    public function transform($subject, AbstractTransformer $transformer): array;
}
