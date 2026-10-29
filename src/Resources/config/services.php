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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Dmytrof\FractalBundle\DependencyInjection\DmytrofFractalExtension;
use Dmytrof\FractalBundle\Service\{ExtensionsContainer, FractalManager, ScopeFactory, TransformersContainer};
use Dmytrof\FractalBundle\ViewHandler\FractalHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(ScopeFactory::class);

    $services->set(ExtensionsContainer::class)
        ->arg('$extensions', tagged_iterator(DmytrofFractalExtension::TRANSFORMER_EXTENSION_TAG));

    $services->set(TransformersContainer::class)
        ->arg('$transformers', tagged_iterator(DmytrofFractalExtension::TRANSFORMER_TAG));

    $services->set(FractalManager::class);

    // Registered as "json" handler of FOSRestBundle's view handler by FOSRestPass
    $services->set(FractalHandler::class);
};
