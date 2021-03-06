<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\FractalBundle\Serializer;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

class SimpleArraySerializer extends BaseArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey) {
            return [$resourceKey  => $data];
        }
        return $data;
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array|null
     */
    public function item($resourceKey, array $data)
    {
        if (!$data) {
            return null;
        }
        return $data;
    }

    /**
     * Serialize null resource.
     *
     * @return null
     */
    public function null()
    {
        return null;
    }
}