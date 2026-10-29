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

use Dmytrof\FractalBundle\Transformer\Extension\ExtensionInterface;
use Doctrine\Common\Collections\{ArrayCollection, Collection};

/**
 * @implements \IteratorAggregate<int, ExtensionInterface>
 */
class ExtensionsContainer implements \IteratorAggregate, \Countable
{
    /**
     * @var Collection<int, ExtensionInterface>
     */
    protected Collection $extensions;

    /**
     * @param iterable<ExtensionInterface> $extensions
     */
    public function __construct(iterable $extensions = [])
    {
        $this->extensions = new ArrayCollection();
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * @return \ArrayIterator<int, ExtensionInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->extensions->toArray());
    }

    public function count(): int
    {
        return $this->extensions->count();
    }

    /**
     * Adds extension
     */
    public function addExtension(ExtensionInterface $extension): static
    {
        $this->extensions->add($extension);

        return $this;
    }
}
