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
namespace KiwiSuite\ApplicationHttp\Middleware\Factory;

use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\ApplicationHttp\Middleware\SegmentMiddlewarePipe;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Uri;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\PathBasedRoutingMiddleware;
use Zend\Stratigility\Middleware\CallableMiddlewareDecorator;
use Zend\Stratigility\Middleware\PathMiddlewareDecorator;

final class SegmentMiddlewareFactory implements FactoryInterface
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
        if ($options === null) {
            //todo exception
        }

        if (!isset($options[PipeConfig::class]) || !($options[PipeConfig::class] instanceof PipeConfig)) {
            //todo exception
        }

        $routerKey = (!empty($options['router'])) ? $options['router'] : FastRouteRouter::class;
        $fastRouter = $container->get($routerKey);

        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($container->get(MiddlewareSubManager::class)));

        $segmentMiddlewarePipe = new SegmentMiddlewarePipe();

        /** @var PipeConfig $pipeConfig */
        $pipeConfig = $options[PipeConfig::class];
        foreach ($pipeConfig->getMiddlewarePipe() as $pipeData) {
            switch ($pipeData['type']) {
                case PipeConfig::TYPE_PIPE:
                    $segmentMiddlewarePipe->pipe($middlewareFactory->prepare($pipeData['value']));
                    break;
                case PipeConfig::TYPE_ROUTING:
                    $callableMiddleware = new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($pipeConfig, $middlewareFactory, $fastRouter) {
                        $routeMiddleware = new PathBasedRoutingMiddleware($fastRouter);
                        foreach ($pipeConfig->getRoutes() as $route) {
                            $expressiveRoute = $routeMiddleware->route(
                                $route['path'],
                                $middlewareFactory->pipeline($route['pipe']),
                                $route['methods'],
                                $route['name']
                            );
                            $expressiveRoute->setOptions($route['options']);
                        }

                        return $routeMiddleware->process($request, $handler);
                    });

                    $segmentMiddlewarePipe->pipe($callableMiddleware);
                    break;
                case PipeConfig::TYPE_DISPATCHING:
                    $segmentMiddlewarePipe->pipe($middlewareFactory->lazy(DispatchMiddleware::class));
                    break;

                case PipeConfig::TYPE_SEGMENT:
                    $segmentMiddlewarePipe->pipe($this->getSegmentMiddleware($container, $pipeData, $routerKey));
                    break;
            }
        }

        return $segmentMiddlewarePipe;
    }

    private function getSegmentMiddleware(ServiceManagerInterface $container, array $pipeData, $fastRouterKey): CallableMiddlewareDecorator
    {
        return new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($container, $pipeData, $fastRouterKey) {
            $uri = new Uri($pipeData['value']['segment']);
            if (!empty($uri->getScheme()) && $uri->getScheme() !== $request->getUri()->getScheme()) {
                return $handler->handle($request);
            }

            if (!empty($uri->getHost()) && $uri->getHost() !== $request->getUri()->getHost()) {
                return $handler->handle($request);
            }

            if (!empty($uri->getPort()) && $uri->getPort() !== $request->getUri()->getPort()) {
                return $handler->handle($request);
            }

            $segmentMiddleware = $container
                ->get(MiddlewareSubManager::class)
                ->build(
                    SegmentMiddlewarePipe::class,
                    [
                        PipeConfig::class => $pipeData['value']['pipeConfig'],
                        'router' => $fastRouterKey,
                    ]
                );
            $pathMiddlewareDecorator = new PathMiddlewareDecorator($uri->getPath(), $segmentMiddleware);
            return $pathMiddlewareDecorator->process($request, $handler);
        });
    }
}
