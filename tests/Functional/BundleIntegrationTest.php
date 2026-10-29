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

namespace Dmytrof\FractalBundle\Tests\Functional;

use Dmytrof\FractalBundle\Service\{ExtensionsContainer, FractalManager, TransformersContainer};
use Dmytrof\FractalBundle\Tests\Data\{Foo, FooTransformer, TestDataExtension};
use Dmytrof\FractalBundle\Tests\Functional\App\TestKernel;
use Dmytrof\FractalBundle\ViewHandler\FractalHandler;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\{View, ViewHandlerInterface};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The bundle in a real kernel with FrameworkBundle and FOSRestBundle
 */
class BundleIntegrationTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', true, $options['fos_rest'] ?? true);
    }

    private function foo(): Foo
    {
        $foo = new Foo();
        $foo->bar = 'bar';
        $foo->baz = 'baz';

        return $foo;
    }

    public function testTransformersAndExtensionsAreCollected(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $transformers = $container->get('test.transformers_container');
        $this->assertInstanceOf(TransformersContainer::class, $transformers);
        $this->assertSame([FooTransformer::class], array_keys(iterator_to_array($transformers)));
        $this->assertSame($container->get(FooTransformer::class), $transformers->get(FooTransformer::class));

        $extensions = $container->get('test.extensions_container');
        $this->assertInstanceOf(ExtensionsContainer::class, $extensions);
        $this->assertSame([TestDataExtension::class], array_map(get_class(...), iterator_to_array($extensions)));
    }

    public function testFractalManager(): void
    {
        self::bootKernel();
        $manager = static::getContainer()->get('test.fractal_manager');
        $this->assertInstanceOf(FractalManager::class, $manager);

        $this->assertSame([
            'bar' => 'bar',
            'baz' => 'baz',
            TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA,
        ], $manager->getModelData($this->foo(), FooTransformer::class));

        $this->assertSame([
            'bar' => 'bar',
            'baz' => 'baz',
            'boo' => Foo::BOO_DATA,
            TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA,
        ], (clone $manager)->parseIncludes('boo')->getModelData($this->foo(), FooTransformer::class));
    }

    public function testFractalHandlerIsRegisteredInFOSRestViewHandler(): void
    {
        self::bootKernel();
        $viewHandler = static::getContainer()->get('test.fos_rest.view_handler');
        $this->assertInstanceOf(ViewHandlerInterface::class, $viewHandler);
        $manager = static::getContainer()->get('test.fractal_manager');

        $view = View::create($manager->getModelData($this->foo(), FooTransformer::class));
        $view->setFormat('json')->setContext((new Context())->setAttribute(FractalHandler::ATTRIBUTE, true));

        $response = $viewHandler->handle($view, new Request());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['bar' => 'bar', 'baz' => 'baz', TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA]),
            $response->getContent(),
        );
    }

    public function testRegularViewIsSerializedByFOSRest(): void
    {
        self::bootKernel();
        $viewHandler = static::getContainer()->get('test.fos_rest.view_handler');

        $response = $viewHandler->handle(View::create(['foo' => 'bar'])->setFormat('json'), new Request());

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"foo":"bar"}', $response->getContent());
    }

    public function testKernelWithoutFOSRestBundle(): void
    {
        self::bootKernel(['fos_rest' => false]);
        $container = static::getContainer();

        $this->assertFalse($container->has('fos_rest.view_handler'));
        $this->assertSame([
            'bar' => 'bar',
            'baz' => 'baz',
            TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA,
        ], $container->get('test.fractal_manager')->getModelData($this->foo(), FooTransformer::class));
    }
}
