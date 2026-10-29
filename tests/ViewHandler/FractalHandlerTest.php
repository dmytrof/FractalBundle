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

namespace Dmytrof\FractalBundle\Tests\ViewHandler;

use Dmytrof\FractalBundle\ViewHandler\FractalHandler;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\{View, ViewHandlerInterface};
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{Request, Response};

class FractalHandlerTest extends TestCase
{
    public function testFractalView(): void
    {
        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->expects($this->once())
            ->method('createResponse')
            ->with($this->callback(static fn (View $view): bool => null === $view->getData()))
            ->willReturn(new Response('', Response::HTTP_NO_CONTENT));

        $view = View::create(['foo' => 'bar', 'test' => 4, 'name' => 'Дмитро']);
        $view->setContext((new Context())->setAttribute(FractalHandler::ATTRIBUTE, true));

        $response = (new FractalHandler())($viewHandler, $view, new Request(), 'json');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('{"foo":"bar","test":4,"name":"Дмитро"}', $response->getContent());
    }

    public function testFractalViewWithoutData(): void
    {
        $viewHandler = $this->createStub(ViewHandlerInterface::class);
        $viewHandler->method('createResponse')->willReturn(new Response('', Response::HTTP_NO_CONTENT));

        $view = View::create();
        $view->setContext((new Context())->setAttribute(FractalHandler::ATTRIBUTE, true));

        $response = (new FractalHandler())($viewHandler, $view, new Request(), 'json');

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testRegularView(): void
    {
        $view = View::create(['foo' => 'bar']);
        $expected = new Response('serialized');

        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->expects($this->once())->method('createResponse')->with($view)->willReturn($expected);

        $this->assertSame($expected, (new FractalHandler())($viewHandler, $view, new Request(), 'json'));
        $this->assertSame(['foo' => 'bar'], $view->getData());
    }
}
