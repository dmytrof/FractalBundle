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

use Dmytrof\FractalBundle\Exception\TransformerException;
use Dmytrof\FractalBundle\Serializer\SimpleArraySerializer;
use Dmytrof\FractalBundle\Transformer\AbstractTransformer;
use Doctrine\Common\Collections\{ArrayCollection, Collection as DoctrineCollection};
use League\Fractal\{Manager as BaseManager, Resource\Collection, Resource\Item, TransformerAbstract};

/**
 * Transformers may be passed as instances, callables or class names of transformers registered in the container
 */
class FractalManager extends BaseManager
{
    public const CODE = 'fractal';

    public function __construct(
        protected TransformersContainer $transformersContainer,
        ?ScopeFactory $scopeFactory = null,
    ) {
        parent::__construct($scopeFactory ?? new ScopeFactory());
        $this->setSerializer(new SimpleArraySerializer());
    }

    /**
     * Returns paginator data
     */
    public function getPaginatorData(iterable $iterator, TransformerAbstract|callable|string $transformer): array
    {
        return $this->getCollectionData($iterator, $transformer);
    }

    /**
     * Returns model data (null for a null model)
     */
    public function getModelData(mixed $model, TransformerAbstract|callable|string $transformer): ?array
    {
        return $this->createData(new Item($model, $transformer))->toArray();
    }

    /**
     * Returns collection data
     */
    public function getCollectionData(iterable $data, TransformerAbstract|callable|string $transformer): array
    {
        return $this->createData(new Collection($data, $transformer))->toArray() ?? [];
    }

    /**
     * Returns transformer by class name
     *
     * @param class-string<AbstractTransformer> $transformerClass
     *
     * @throws TransformerException
     */
    public function getTransformer(string $transformerClass): AbstractTransformer
    {
        return $this->transformersContainer->get($transformerClass);
    }

    /**
     * Returns transformers supporting the subject
     *
     * @return DoctrineCollection<int, AbstractTransformer>
     *
     * @throws TransformerException
     */
    public function getTransformersForSubject(mixed $subject): DoctrineCollection
    {
        $collection = new ArrayCollection();
        foreach ($this->transformersContainer as $transformer) {
            if ($transformer->supports($subject)) {
                $collection->add($transformer);
            }
        }
        if ($collection->count()) {
            return $collection;
        }

        throw new TransformerException(sprintf('There is no transformer for subject %s', is_scalar($subject) ? (string) $subject : get_debug_type($subject)));
    }
}
