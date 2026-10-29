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

use Dmytrof\FractalBundle\Transformer\{AbstractTransformer, Extension\AbstractExtension};
use League\Fractal\Resource\{Primitive, ResourceInterface};

class TestDataExtension extends AbstractExtension
{
    public const TEST_DATA_INCLUDE = 'testData';

    protected function _supports(\ReflectionClass $reflectionClass, AbstractTransformer $transformer): bool
    {
        return $reflectionClass->implementsInterface(TestDataInterface::class);
    }

    public function decorateTransformer(AbstractTransformer $transformer): void
    {
        $transformer
            ->addAvailableInclude(self::TEST_DATA_INCLUDE)
            ->addDefaultInclude(self::TEST_DATA_INCLUDE)
            ->setIncludeCall(self::TEST_DATA_INCLUDE, $this->includeTestData(...))
        ;
    }

    public function includeTestData(TestDataInterface $subject, AbstractTransformer $transformer): ResourceInterface
    {
        return new Primitive($subject->getTestData());
    }
}
