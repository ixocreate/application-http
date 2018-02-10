<?php
namespace KiwiSuite\ApplicationHttp\Middleware\Factory;

use function FastRoute\TestFixtures\empty_options_cached;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\ApplicationHttp\Middleware\SegmentMiddlewarePipe;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ServiceManager\FactoryInterface;
use KiwiSuite\ServiceManager\ServiceManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Router\DispatchMiddleware;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\PathBasedRoutingMiddleware;
use Zend\Stratigility\Middleware\CallableMiddlewareDecorator;
use Zend\Stratigility\Middleware\PathMiddlewareDecorator;

final class SegmentMiddlewareFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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
                    $segmentMiddlewarePipe->pipe(new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($pipeConfig, $middlewareFactory, $fastRouter){
                        $routeMiddleware = new PathBasedRoutingMiddleware($fastRouter, new Response());
                        foreach ($pipeConfig->getRoutes() as $route) {
                            $routeMiddleware->route($route['path'], $middlewareFactory->pipeline($route['pipe']), $route['methods'], $route['name']);
                        }

                        return $routeMiddleware->process($request, $handler);
                    }));
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
        return new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($container, $pipeData, $fastRouterKey){
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
                        'router' => $fastRouterKey
                    ]
                );
            $pathMiddlewareDecorator = new PathMiddlewareDecorator($uri->getPath(), $segmentMiddleware);
            return $pathMiddlewareDecorator->process($request, $handler);
        });
    }
}
