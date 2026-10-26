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

use ArrayIterator;
use Dmytrof\FractalBundle\{Exception\TransformerException, Transformer\AbstractTransformer};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Traversable;

class TransformersContainer implements \IteratorAggregate, \Countable
{
    /**
     * @var Collection|AbstractTransformer[]
     */
    protected $transformers;

    /**
     * TransformersContainer constructor.
     * @param iterable $transformers
     */
    public function __construct(iterable $transformers)
    {
        $this->transformers = new ArrayCollection();
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->transformers->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->transformers);
    }

    /**
     * Adds transformer
     * @param AbstractTransformer $transformer
     * @return TransformersContainer
     */
    public function addTransformer(AbstractTransformer $transformer): self
    {
        $this->transformers->set(get_class($transformer), $transformer);
        return $this;
    }

    /**
     * Returns transformer by class name
     * @param string $className
     * @return AbstractTransformer
     */
    public function get(string $className): AbstractTransformer
    {
        if (!$this->has($className)) {
            throw new TransformerException(sprintf('Undefined transformer with class %s', $className));
        }
        return $this->transformers->get($className);
    }

    /**
     * Checks if container has transformer with class name
     * @param string $className
     * @return bool
     */
    public function has(string $className): bool
    {
        return $this->transformers->containsKey($className);
    }
}