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
use League\Fractal\Resource\ResourceInterface;

class FooTransformer extends AbstractTransformer
{
    protected const SUBJECT_CLASS = Foo::class;

    protected array $availableIncludes = [
        'boo',
        'child',
    ];

    public function transformSubject($subject): array
    {
        return [
            'bar' => $subject->bar,
            'baz' => $subject->baz,
        ];
    }

    public function includeBoo(Foo $subject): ResourceInterface
    {
        return $this->primitive($subject->getBooData());
    }

    public function includeChild(Foo $subject): ResourceInterface
    {
        // Transformer class name is resolved by FractalManager
        return $this->item($subject->child, self::class);
    }
}
