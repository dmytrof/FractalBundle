<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\FractalBundle\Service;

use Dmytrof\FractalBundle\Transformer\Extension\ExtensionInterface;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Traversable;

class ExtensionsContainer implements \IteratorAggregate
{
    /**
     * @var Collection|ExtensionInterface[]
     */
    protected $extensions;

    /**
     * ExtensionsContainer constructor.
     * @param iterable $extensions
     */
    public function __construct(iterable $extensions)
    {
        $this->extensions = new ArrayCollection();
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->extensions->toArray());
    }

    /**
     * Adds extension
     * @param ExtensionInterface $extension
     * @return ExtensionsContainer
     */
    public function addExtension(ExtensionInterface $extension): self
    {
        $this->extensions->add($extension);
        return $this;
    }
}