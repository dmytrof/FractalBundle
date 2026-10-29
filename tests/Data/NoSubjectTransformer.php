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

namespace Dmytrof\FractalBundle\Tests\Data;

use Dmytrof\FractalBundle\Transformer\AbstractTransformer;

class NoSubjectTransformer extends AbstractTransformer
{
    public function transformSubject($subject): array
    {
        return [];
    }
}
