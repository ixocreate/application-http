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
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Expressive\Application;
use Zend\Expressive\Emitter\EmitterStack;
use Zend\Expressive\Middleware\LazyLoadingMiddleware;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\RouterInterface;
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

        if ($options !== null && \array_key_exists(RouterInterface::class, $options) && $options[RouterInterface::class] instanceof RouterInterface) {
            $router =  $options[RouterInterface::class];
        } else {
            $router = new FastRouteRouter();
        }

        $application = new $requestedName(
            $router,
            $container->get(MiddlewareSubManager::class),
            null,
            $emitter
        );

        if ($options !== null && \array_key_exists(PipeConfig::class, $options) && $options[PipeConfig::class] instanceof PipeConfig) {
            $this->addPipes($application, $options[PipeConfig::class], $container);
        }

        if ($options !== null && \array_key_exists(RouteConfig::class, $options) && $options[RouteConfig::class] instanceof RouteConfig) {
            $this->addRoutes($application, $options[RouteConfig::class], $container);
        }

        return $application;
    }

    /**
     * @param Application $application
     * @param PipeConfig $pipeConfig
     * @param ServiceManagerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function addPipes(Application $application, PipeConfig $pipeConfig, ServiceManagerInterface $container) : void
    {
        foreach ($pipeConfig->getGlobalPipe() as $globalItem) {
            $middleware = $this->createMiddlewarePipe($globalItem['middlewares'], $container);
            if ($globalItem['path'] !== null) {
                $middleware = new PathMiddlewareDecorator($globalItem['path'], $middleware);
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

    /**
     * @param Application $application
     * @param RouteConfig $routeConfig
     * @param ServiceManagerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function addRoutes(Application $application, RouteConfig $routeConfig, ServiceManagerInterface $container): void
    {
        foreach ($routeConfig->getRoutes() as $route) {
            $application->route(
                $route['path'],
                $this->createMiddlewarePipe($route['middlewares'], $container),
                $route['methods'],
                $route['name']
            );
        }
    }

    /**
     * @param array $middlewares
     * @param ServiceManagerInterface $container
     * @return LazyLoadingMiddleware|MiddlewarePipe
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createMiddlewarePipe(array $middlewares, ServiceManagerInterface $container)
    {
        if (count($middlewares) === 1) {
            return new LazyLoadingMiddleware(
                $container->get(MiddlewareSubManager::class),
                new Response(),
                array_pop($middlewares)
            );
        }

        $middlewarePipe = new MiddlewarePipe();
        foreach ($middlewares as $middlware) {
            $lazyMiddleware = new LazyLoadingMiddleware(
                $container->get(MiddlewareSubManager::class),
                new Response(),
                $middlware
            );

            $middlewarePipe->pipe($lazyMiddleware);
        }

        return $middlewarePipe;
    }
}
