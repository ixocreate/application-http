<?php
/**
 * kiwi-suite/application-http (https://github.com/kiwi-suite/application-http)
 *
 * @package kiwi-suite/application-http
 * @see https://github.com/kiwi-suite/application-http
 * @copyright Copyright (c) 2010 - 2017 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);
namespace KiwiSuite\ApplicationHttp\Factory;

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ApplicationHttp\Route\RouteConfig;
use KiwiSuite\ServiceManager\FactoryInterface;
use KiwiSuite\ServiceManager\ServiceManagerInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Expressive\Application;
use Zend\Expressive\Emitter\EmitterStack;
use Zend\Expressive\Router\FastRouteRouter;

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

        $application = new Application(
            new FastRouteRouter(),
            $container->get(MiddlewareSubManager::class),
            null,
            $emitter
        );

        if ($options !== null && \array_key_exists(PipeConfig::class, $options) && $options[PipeConfig::class] instanceof PipeConfig) {
            $this->addPipes($application, $options[PipeConfig::class]);
        }

        if ($options !== null && \array_key_exists(RouteConfig::class, $options) && $options[RouteConfig::class] instanceof RouteConfig) {
            $this->addRoutes($application, $options[RouteConfig::class]);
        }

        return $application;
    }

    private function addPipes(Application $application, PipeConfig $pipeConfig) : void
    {
        foreach ($pipeConfig->getGlobalPipe() as $middleware) {
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
                $route['middleware'],
                $route['methods'],
                $route['name']
            );
        }
    }
}
