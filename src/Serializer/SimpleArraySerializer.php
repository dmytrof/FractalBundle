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

namespace Dmytrof\FractalBundle\Serializer;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

/**
 * Collections are not wrapped by "data" key (unless resource key is set), items are returned as is.
 * Empty items are serialized as null by Dmytrof\FractalBundle\Scope\Scope.
 */
class SimpleArraySerializer extends BaseArraySerializer
{
    public function collection(?string $resourceKey, array $data): array
    {
        if ($resourceKey) {
            return [$resourceKey => $data];
        }

        return $data;
    }

    public function item(?string $resourceKey, array $data): array
    {
        return $data;
    }

    public function null(): ?array
    {
        return null;
    }
}
