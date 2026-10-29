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

namespace Dmytrof\FractalBundle\Tests\DependencyInjection;

use Dmytrof\FractalBundle\DependencyInjection\Compiler\FOSRestPass;
use Dmytrof\FractalBundle\DependencyInjection\{Configuration, DmytrofFractalExtension};
use Dmytrof\FractalBundle\DmytrofFractalBundle;
use Dmytrof\FractalBundle\Service\{ExtensionsContainer, FractalManager, ScopeFactory, TransformersContainer};
use Dmytrof\FractalBundle\Tests\Data\{Foo, FooTransformer, TestDataExtension};
use Dmytrof\FractalBundle\Transformer\{AbstractTransformer, Extension\ExtensionInterface};
use Dmytrof\FractalBundle\ViewHandler\FractalHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition, Reference};

class DmytrofFractalExtensionTest extends TestCase
{
    private function load(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new DmytrofFractalExtension())->load([], $container);

        return $container;
    }

    public function testAlias(): void
    {
        $this->assertSame('dmytrof_fractal', (new DmytrofFractalExtension())->getAlias());
        $this->assertInstanceOf(DmytrofFractalExtension::class, (new DmytrofFractalBundle())->getContainerExtension());
    }

    public function testConfiguration(): void
    {
        $this->assertSame([], (new Processor())->processConfiguration(new Configuration(), [[]]));
        $this->assertSame('dmytrof_fractal', (new Configuration())->getConfigTreeBuilder()->buildTree()->getName());
    }

    public function testServices(): void
    {
        $container = $this->load();

        foreach ([ScopeFactory::class, ExtensionsContainer::class, TransformersContainer::class, FractalManager::class, FractalHandler::class] as $id) {
            $this->assertTrue($container->hasDefinition($id), $id);
            $this->assertFalse($container->getDefinition($id)->isPublic(), $id);
        }

        $extensions = $container->getDefinition(ExtensionsContainer::class)->getArgument('$extensions');
        $this->assertInstanceOf(TaggedIteratorArgument::class, $extensions);
        $this->assertSame(DmytrofFractalExtension::TRANSFORMER_EXTENSION_TAG, $extensions->getTag());

        $transformers = $container->getDefinition(TransformersContainer::class)->getArgument('$transformers');
        $this->assertInstanceOf(TaggedIteratorArgument::class, $transformers);
        $this->assertSame(DmytrofFractalExtension::TRANSFORMER_TAG, $transformers->getTag());
    }

    public function testAutoconfiguration(): void
    {
        $autoconfigured = $this->load()->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(DmytrofFractalExtension::TRANSFORMER_TAG, $autoconfigured[AbstractTransformer::class]->getTags());
        $this->assertArrayHasKey(DmytrofFractalExtension::TRANSFORMER_EXTENSION_TAG, $autoconfigured[ExtensionInterface::class]->getTags());
    }

    public function testCompiledContainer(): void
    {
        $container = $this->load();
        $container->register(FooTransformer::class)->setAutowired(true)->setAutoconfigured(true);
        $container->register(TestDataExtension::class)->setAutoconfigured(true);
        $container->setAlias('fractal_manager', FractalManager::class)->setPublic(true);
        $container->compile();

        $this->assertSame(
            ['bar' => null, 'baz' => null, TestDataExtension::TEST_DATA_INCLUDE => Foo::TEST_DATA],
            $container->get('fractal_manager')->getModelData(new Foo(), FooTransformer::class),
        );
    }

    public function testFOSRestPass(): void
    {
        $container = $this->load();
        $viewHandler = $container->setDefinition(FOSRestPass::VIEW_HANDLER_ID, new Definition(\stdClass::class));

        (new FOSRestPass())->process($container);

        $this->assertEquals([['registerHandler', ['json', new Reference(FractalHandler::class)]]], $viewHandler->getMethodCalls());
    }

    public function testFOSRestPassWithoutFOSRest(): void
    {
        $container = $this->load();

        (new FOSRestPass())->process($container);

        $this->assertFalse($container->hasDefinition(FOSRestPass::VIEW_HANDLER_ID));
    }

    public function testBundleRegistersFOSRestPass(): void
    {
        $container = new ContainerBuilder();
        (new DmytrofFractalBundle())->build($container);

        $passes = array_map(get_class(...), $container->getCompilerPassConfig()->getBeforeOptimizationPasses());
        $this->assertContains(FOSRestPass::class, $passes);
    }
}
