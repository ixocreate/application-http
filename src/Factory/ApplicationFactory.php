<?php
/**
 * kiwi-suite/application-http (https://github.com/kiwi-suite/application-http)
 *
 * @package kiwi-suite/application-http
 * @see https://github.com/kiwi-suite/application-http
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);
namespace KiwiSuite\ApplicationHttp\Factory;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\ApplicationHttp\Middleware\PathMiddlewareDecorator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ApplicationHttp\Route\RouteConfig;
use KiwiSuite\ServiceManager\FactoryInterface;
use KiwiSuite\ServiceManager\ServiceManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Expressive\Application;
use Zend\Expressive\Emitter\EmitterStack;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Stratigility\MiddlewarePipe;

final class ApplicationFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $emitter = new EmitterStack();
        $emitter->push(new SapiEmitter());

        $application = new $requestedName(
            new FastRouteRouter(),
            $container->get(MiddlewareSubManager::class),
            null,
            $emitter
        );

        if ($options !== null && \array_key_exists(PipeConfig::class, $options) && $options[PipeConfig::class] instanceof PipeConfig) {
            $this->addPipes($application, $options[PipeConfig::class], $container);
        }

        if ($options !== null && \array_key_exists(RouteConfig::class, $options) && $options[RouteConfig::class] instanceof RouteConfig) {
            $this->addRoutes($application, $options[RouteConfig::class]);
        }

        return $application;
    }

    private function addPipes(Application $application, PipeConfig $pipeConfig, ServiceManagerInterface $container) : void
    {
        foreach ($pipeConfig->getGlobalPipe() as $globalItem) {
            $middleware = $this->createMiddlewarePipe($globalItem['middlewares']);
            $originalMiddleware = $middleware;
            if ($globalItem['path'] !== null) {
                $middleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($originalMiddleware, $globalItem, $container) {
                    if (is_string($originalMiddleware)) {
                        $originalMiddleware = $container->get(MiddlewareSubManager::class)->get($originalMiddleware);
                    }
                    $pathMiddleware = new PathMiddlewareDecorator($globalItem['path'], $originalMiddleware);
                    return $pathMiddleware->process($request, $handler);
                };
            }
            $application->pipe($middleware);
        }

        $application->pipeRoutingMiddleware();
        foreach ($pipeConfig->getRoutingPipe() as $middleware) {
            $application->pipe($middleware);
        }

        $application->pipeDispatchMiddleware();
        foreach ($pipeConfig->getDispatchPipe() as $middleware) {
            $application->pipe($middleware);
        }
    }

    private function addRoutes(Application $application, RouteConfig $routeConfig): void
    {
        foreach ($routeConfig->getRoutes() as $route) {
            $application->route(
                $route['path'],
                $this->createMiddlewarePipe($route['middlewares']),
                $route['methods'],
                $route['name']
            );
        }
    }

    private function createMiddlewarePipe(array $middlewares)
    {
        if (count($middlewares) === 1) {
            return array_pop($middlewares);
        }

        $middlewarePipe = new MiddlewarePipe();
        foreach ($middlewares as $middlware) {
            $middlewarePipe->pipe($middlware);
        }

        return $middlewarePipe;
    }
}
