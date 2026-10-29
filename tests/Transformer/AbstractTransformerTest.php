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

namespace Dmytrof\FractalBundle\Tests\Transformer;

use Dmytrof\FractalBundle\Exception\TransformerException;
use Dmytrof\FractalBundle\Service\ExtensionsContainer;
use Dmytrof\FractalBundle\Tests\Data\{Foo, FooTransformer, NoSubjectTransformer, StampExtension, TestDataExtension, TestExtension};
use PHPUnit\Framework\TestCase;

class AbstractTransformerTest extends TestCase
{
    private FooTransformer $transformer;

    protected function setUp(): void
    {
        $extensionContainer = new ExtensionsContainer([new TestExtension(), new TestDataExtension()]);
        $this->transformer = new FooTransformer($extensionContainer);
        $this->transformer->setup();
    }

    public function testCreatedTransformer(): void
    {
        $this->assertInstanceOf(ExtensionsContainer::class, $this->transformer->getExtensionsContainer());
        $this->assertCount(2, $this->transformer->getExtensionsContainer());
        $this->assertSame([TestDataExtension::class], $this->transformer->getExtensions()->getKeys());
        $this->assertSame(Foo::class, $this->transformer->getSubjectClass());

        $this->assertSame(['boo', 'child', TestDataExtension::TEST_DATA_INCLUDE], $this->transformer->getAvailableIncludes());
        $this->assertSame([TestDataExtension::TEST_DATA_INCLUDE], $this->transformer->getDefaultIncludes());
    }

    public function testSetupDecoratesOnce(): void
    {
        $this->transformer->setup()->setup();

        $this->assertSame([TestDataExtension::TEST_DATA_INCLUDE], $this->transformer->getDefaultIncludes());
    }

    public function testTransform(): void
    {
        $foo = new Foo();
        $foo->bar = 'baz';
        $foo->baz = 'jazz';

        $this->assertTrue($this->transformer->supports($foo));
        $this->assertTrue($this->transformer->supports(null));
        $this->assertFalse($this->transformer->supports(new \stdClass()));

        $this->assertSame([
            'bar' => 'baz',
            'baz' => 'jazz',
        ], $this->transformer->transform($foo));
        $this->assertSame([], $this->transformer->transform(null));
    }

    public function testTransformWithExtensionData(): void
    {
        $foo = new Foo();
        $foo->bar = 'baz';

        $transformer = new FooTransformer(new ExtensionsContainer([new StampExtension()]));

        $this->assertSame(['bar' => 'baz', 'baz' => null, 'stamp' => 'stamped'], $transformer->transform($foo));
    }

    public function testTransformUnsupportedSubject(): void
    {
        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage(FooTransformer::class.' transforms '.Foo::class.' subjects only.');

        $this->transformer->transform(new \stdClass());
    }

    public function testUndefinedSubjectClass(): void
    {
        $transformer = new NoSubjectTransformer(new ExtensionsContainer([new StampExtension()]));
        $this->assertCount(0, $transformer->getExtensions());

        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage(NoSubjectTransformer::class.'::SUBJECT_CLASS is not defined.');
        $transformer->getSubjectClass();
    }

    public function testAvailableIncludes(): void
    {
        $this->transformer->addAvailableInclude('bar', 'baz');
        $this->assertSame(['boo', 'child', TestDataExtension::TEST_DATA_INCLUDE, 'bar', 'baz'], $this->transformer->getAvailableIncludes());

        $this->transformer->removeAvailableInclude('baz', 'bar');
        $this->assertSame(['boo', 'child', TestDataExtension::TEST_DATA_INCLUDE], $this->transformer->getAvailableIncludes());

        $includes = ['foo', 'bar', 'baz'];
        $this->assertSame($includes, $this->transformer->setAvailableIncludes($includes)->getAvailableIncludes());
    }

    public function testDefaultIncludes(): void
    {
        $this->transformer->addDefaultInclude('bar', 'baz');
        $this->assertSame([TestDataExtension::TEST_DATA_INCLUDE, 'bar', 'baz'], $this->transformer->getDefaultIncludes());

        $this->transformer->removeDefaultInclude('baz', 'bar');
        $this->assertSame([TestDataExtension::TEST_DATA_INCLUDE], $this->transformer->getDefaultIncludes());

        $includes = ['foo', 'bar', 'baz'];
        $this->assertSame($includes, $this->transformer->setDefaultIncludes($includes)->getDefaultIncludes());
        $this->assertSame([], $this->transformer->resetDefaultIncludes()->getDefaultIncludes());
    }

    public function testIncludeCalls(): void
    {
        $this->assertCount(1, $this->transformer->getIncludeCalls());

        $this->transformer->setIncludeCall('bar', static fn () => 'bar');
        $this->assertCount(2, $this->transformer->getIncludeCalls());

        $calls = [
            'bar' => static fn () => 'bar',
            'baz' => static fn () => 'baz',
        ];
        $this->assertSame($calls, $this->transformer->setIncludeCalls($calls)->getIncludeCalls());
        $this->assertTrue($this->transformer->hasIncludeCall('bar'));
        $this->assertFalse($this->transformer->hasIncludeCall('test'));
        $this->assertSame($calls['baz'], $this->transformer->getIncludeCall('baz'));
        $this->assertNull($this->transformer->getIncludeCall('test'));

        $this->transformer->removeIncludeCall('bar');
        $this->assertFalse($this->transformer->hasIncludeCall('bar'));
        $this->assertCount(1, $this->transformer->getIncludeCalls());
    }

    public function testIncludeToRoot(): void
    {
        $this->assertSame([], $this->transformer->getIncludesToRoot());

        $this->transformer->setIncludesToRoot(['bar', 'baz']);
        $this->assertSame(['bar', 'baz'], $this->transformer->getIncludesToRoot());

        $this->assertSame(['bar', 'baz', 'bar1', 'baz1'], $this->transformer->addIncludeToRoot('bar1', 'baz1')->getIncludesToRoot());
        $this->assertTrue($this->transformer->hasIncludeToRoot('bar1'));
        $this->assertFalse($this->transformer->removeIncludeToRoot('bar1', 'baz1')->hasIncludeToRoot('bar1'));
        $this->assertSame(['bar', 'baz'], $this->transformer->getIncludesToRoot());
    }

    public function testTransformDateTime(): void
    {
        $dateTime = new \DateTimeImmutable('2026-01-02 03:04:05', new \DateTimeZone('UTC'));

        $this->assertSame('2026-01-02T03:04:05+00:00', $this->transformer->transformDateTime($dateTime));
        $this->assertSame('2026-01-02', $this->transformer->transformDateTime($dateTime, 'Y-m-d'));
        $this->assertNull($this->transformer->transformDateTime(null));
    }
}
