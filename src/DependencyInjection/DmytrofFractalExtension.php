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

namespace Dmytrof\FractalBundle\DependencyInjection;

use Dmytrof\FractalBundle\Transformer\{AbstractTransformer, Extension\ExtensionInterface};
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DmytrofFractalExtension extends Extension
{
    public const TRANSFORMER_TAG = 'dmytrof.fractal.transformer';
    public const TRANSFORMER_EXTENSION_TAG = 'dmytrof.fractal.transformer.extension';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $container->registerForAutoconfiguration(AbstractTransformer::class)
            ->addTag(self::TRANSFORMER_TAG);

        $container->registerForAutoconfiguration(ExtensionInterface::class)
            ->addTag(self::TRANSFORMER_EXTENSION_TAG);
    }

    public function getAlias(): string
    {
        return Configuration::ROOT_NAME;
    }
}
