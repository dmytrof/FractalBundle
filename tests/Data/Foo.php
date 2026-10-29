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

class Foo implements TestDataInterface
{
    public const TEST_DATA = [
        'hello' => 'world',
    ];
    public const BOO_DATA = ['boo1', 'boo2', 'boo3'];

    public mixed $bar = null;
    public mixed $baz = null;
    public mixed $gaz = null;
    public ?Foo $child = null;

    public function getTestData(): array
    {
        return self::TEST_DATA;
    }

    public function getBooData(): array
    {
        return self::BOO_DATA;
    }
}
