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

namespace Dmytrof\FractalBundle\DependencyInjection\Compiler;

use Dmytrof\FractalBundle\ViewHandler\FractalHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Reference};

/**
 * Registers FractalHandler as "json" handler of FOSRestBundle's view handler
 */
class FOSRestPass implements CompilerPassInterface
{
    public const VIEW_HANDLER_ID = 'fos_rest.view_handler.default';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::VIEW_HANDLER_ID) || !$container->has(FractalHandler::class)) {
            return;
        }

        $container->getDefinition(self::VIEW_HANDLER_ID)
            ->addMethodCall('registerHandler', ['json', new Reference(FractalHandler::class)]);
    }
}
