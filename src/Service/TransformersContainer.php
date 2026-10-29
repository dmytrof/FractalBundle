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

use Dmytrof\FractalBundle\{Exception\TransformerException, Transformer\AbstractTransformer};
use Doctrine\Common\Collections\{ArrayCollection, Collection};

/**
 * @implements \IteratorAggregate<class-string<AbstractTransformer>, AbstractTransformer>
 */
class TransformersContainer implements \IteratorAggregate, \Countable
{
    /**
     * @var Collection<class-string<AbstractTransformer>, AbstractTransformer>
     */
    protected Collection $transformers;

    /**
     * @param iterable<AbstractTransformer> $transformers
     */
    public function __construct(iterable $transformers = [])
    {
        $this->transformers = new ArrayCollection();
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }
    }

    /**
     * @return \ArrayIterator<class-string<AbstractTransformer>, AbstractTransformer>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->transformers->toArray());
    }

    public function count(): int
    {
        return $this->transformers->count();
    }

    /**
     * Adds transformer
     */
    public function addTransformer(AbstractTransformer $transformer): static
    {
        $this->transformers->set($transformer::class, $transformer);

        return $this;
    }

    /**
     * Returns transformer by class name
     *
     * @throws TransformerException
     */
    public function get(string $className): AbstractTransformer
    {
        return $this->transformers->get($className)
            ?? throw new TransformerException(sprintf('Undefined transformer with class %s', $className));
    }

    /**
     * Checks if container has transformer with class name
     */
    public function has(string $className): bool
    {
        return $this->transformers->containsKey($className);
    }
}
