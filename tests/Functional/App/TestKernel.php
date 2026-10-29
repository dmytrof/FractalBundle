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

namespace Dmytrof\FractalBundle\Tests\Functional\App;

use Dmytrof\FractalBundle\DmytrofFractalBundle;
use Dmytrof\FractalBundle\Service\{ExtensionsContainer, FractalManager, TransformersContainer};
use Dmytrof\FractalBundle\Tests\Data\{FooTransformer, TestDataExtension};
use FOS\RestBundle\FOSRestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug, private readonly bool $withFosRest = true)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        if ($this->withFosRest) {
            yield new FOSRestBundle();
        }
        yield new DmytrofFractalBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/dmytrof_fractal_bundle_tests/'.($this->withFosRest ? 'fos_rest' : 'plain').'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/dmytrof_fractal_bundle_tests/log';
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'serializer' => ['enabled' => true],
        ]);
        $container->extension('dmytrof_fractal', []);

        $services = $container->services()->defaults()->autowire()->autoconfigure();
        // Application transformer and extension are tagged by autoconfiguration
        $services->set(FooTransformer::class);
        $services->set(TestDataExtension::class);

        // Bundle services are private: public aliases keep them in the test container
        $services->alias('test.fractal_manager', FractalManager::class)->public();
        $services->alias('test.transformers_container', TransformersContainer::class)->public();
        $services->alias('test.extensions_container', ExtensionsContainer::class)->public();
        if ($this->withFosRest) {
            $services->alias('test.fos_rest.view_handler', 'fos_rest.view_handler')->public();
        }
    }
}
