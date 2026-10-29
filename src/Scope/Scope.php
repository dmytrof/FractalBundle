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

namespace Dmytrof\FractalBundle\Scope;

use Dmytrof\FractalBundle\{Exception\TransformerException, Serializer\SimpleArraySerializer, Service\FractalManager, Transformer\AbstractTransformer};
use League\Fractal\Resource\{Item, ResourceInterface};
use League\Fractal\Scope as BaseScope;
use League\Fractal\Serializer\Serializer;

class Scope extends BaseScope
{
    public function __construct(FractalManager $manager, ResourceInterface $resource, ?string $scopeIdentifier = null)
    {
        parent::__construct($manager, $resource, $scopeIdentifier);
    }

    public function getManager(): FractalManager
    {
        return $this->manager;
    }

    /**
     * Allows transformer class names (transformers registered in FractalManager) as transformers,
     * moves "includes to root" to the root of transformed data and sorts the data by keys
     *
     * @param AbstractTransformer|callable|string|null $transformer
     */
    protected function fireTransformer($transformer, $data): array
    {
        if (null === $data) {
            return [[], []];
        }
        if (is_string($transformer) && !is_callable($transformer)) {
            $transformer = $this->getTransformer($transformer)
                ?? throw new TransformerException(sprintf('Undefined transformer with class %s', $transformer));
        }
        [$transformedData, $includedData] = parent::fireTransformer($transformer, $data);
        if ($transformer instanceof AbstractTransformer && $transformer->getIncludesToRoot()) {
            $includesToRoot = array_flip($transformer->getIncludesToRoot());
            $dataToRoot = array_intersect_key($transformedData, $includesToRoot);
            $transformedData = array_diff_key($transformedData, $includesToRoot);
            $transformedData = array_merge($transformedData, ...array_values(array_filter($dataToRoot, is_array(...))));
        }
        ksort($transformedData);

        return [$transformedData, $includedData];
    }

    /**
     * SimpleArraySerializer serializes an empty item (e.g. a null subject) as null
     */
    protected function serializeResource(Serializer $serializer, $data): ?array
    {
        if ($serializer instanceof SimpleArraySerializer && $this->resource instanceof Item && !$data) {
            return $serializer->null();
        }

        return parent::serializeResource($serializer, $data);
    }

    /**
     * Returns transformer service
     */
    protected function getTransformer(string $transformerClass): ?AbstractTransformer
    {
        try {
            return $this->getManager()->getTransformer($transformerClass);
        } catch (TransformerException) {
            return null;
        }
    }
}
