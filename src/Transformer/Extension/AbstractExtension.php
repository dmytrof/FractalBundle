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

abstract class AbstractExtension implements ExtensionInterface
{
    /**
     * Support results cached by subject class
     *
     * @var array<class-string, bool>
     */
    protected array $supports = [];

    public function supports(\ReflectionClass $reflectionClass, AbstractTransformer $transformer): bool
    {
        return $this->supports[$reflectionClass->getName()] ??= $this->_supports($reflectionClass, $transformer);
    }

    /**
     * Checks if extension supports subjects of the class handled by the transformer
     *
     * @param \ReflectionClass<object> $reflectionClass
     */
    protected function _supports(\ReflectionClass $reflectionClass, AbstractTransformer $transformer): bool
    {
        return false;
    }

    public function decorateTransformer(AbstractTransformer $transformer): void
    {
    }

    public function transform($subject, AbstractTransformer $transformer): array
    {
        return [];
    }
}
