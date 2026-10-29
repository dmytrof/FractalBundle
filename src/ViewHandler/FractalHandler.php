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

namespace Dmytrof\FractalBundle\ViewHandler;

use Dmytrof\FractalBundle\Service\FractalManager;
use FOS\RestBundle\View\{View, ViewHandlerInterface};
use Symfony\Component\HttpFoundation\{Request, Response};

/**
 * FOSRestBundle "json" handler: views with the "fractal" context attribute contain data
 * already transformed by Fractal, so it is encoded as is instead of being serialized.
 */
class FractalHandler
{
    public const ATTRIBUTE = FractalManager::CODE;

    public function __invoke(ViewHandlerInterface $viewHandler, View $view, Request $request, string $format): Response
    {
        if (!$view->getContext()->getAttribute(static::ATTRIBUTE)) {
            return $viewHandler->createResponse($view, $request, $format);
        }

        $data = $view->getData();
        $view->setData(null);

        $response = $viewHandler->createResponse($view, $request, $format);
        if (null !== $data) {
            $response->setContent(json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR));
            $response->setStatusCode(Response::HTTP_OK);
        }

        return $response;
    }
}
