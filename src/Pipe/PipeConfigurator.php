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
namespace KiwiSuite\ApplicationHttp\Pipe;

use Zend\Stdlib\SplPriorityQueue;

final class PipeConfigurator
{
    public const PRIORITY_PRE_ROUTING = 1000000;
    public const PRIORITY_POST_ROUTING = 499999;
    public const PRIORITY_POST_DISPATCHING = 999;

    private const PRIORITY_ROUTING = 500000;
    private const PRIORITY_DISPATCHING = 1000;

    /**
     * @var SplPriorityQueue
     */
    private $routesQueue;

    /**
     * @var SplPriorityQueue
     */
    private $middlewareQueue;

    /**
     * @var string
     */
    private $prefix;

    private $segments = [];

    public function __construct(string $prefix = "")
    {
        $this->middlewareQueue = new SplPriorityQueue();
        $this->routesQueue = new SplPriorityQueue();

        $this->middlewareQueue->insert([
            'type' => PipeConfig::TYPE_ROUTING,
        ], self::PRIORITY_ROUTING);

        $this->middlewareQueue->insert([
            'type' => PipeConfig::TYPE_DISPATCHING,
        ], self::PRIORITY_DISPATCHING);

        $this->prefix = $prefix;
    }

    private function validatePriority(int $priority): void
    {
        if ($priority === self::PRIORITY_DISPATCHING || $priority === self::PRIORITY_ROUTING) {
            //TODO Exception
        }
    }

    public function group(callable $callback): void
    {
        $groupPipeConfigurator = new GroupPipeConfigurator();
        $callback($groupPipeConfigurator);

        $before = \array_reverse($groupPipeConfigurator->getBefore());
        $after = $groupPipeConfigurator->getAfter();

        /** @var RouteConfigurator $route */
        foreach ($groupPipeConfigurator->getRoutes() as $route) {
            foreach ($before as $middleware) {
                $route->before($middleware, true);
            }

            foreach ($after as $middleware) {
                $route->after($middleware);
            }

            $this->route($route);
        }
    }

    public function segment(string $segment, callable $callback, int $priority = self::PRIORITY_PRE_ROUTING): void
    {
        if ($priority <= self::PRIORITY_ROUTING) {
            //TODO Exception
        }

        if (!isset($this->segments[$segment])) {
            $this->segments[$segment] = [
                'pipeConfigurator' => new PipeConfigurator($this->prefix . $segment),
                'priority' => $priority,
            ];
        }
        $callback($this->segments[$segment]['pipeConfigurator']);
    }

    public function pipe(string $middleware, int $priority = self::PRIORITY_PRE_ROUTING): void
    {
        $this->validatePriority($priority);
        $this->middlewareQueue->insert([
            'type' => PipeConfig::TYPE_PIPE,
            'value' => $middleware,
        ], $priority);
    }

    public function route(RouteConfigurator $routeConfigurator): void
    {
        $this->routesQueue->insert($routeConfigurator, $routeConfigurator->getPriority());
    }

    public function any(string $path, string $action, string $name, callable $callback = null): void
    {
        $routeConfigurator = new RouteConfigurator($name, $path, $action);
        $routeConfigurator->enableDelete();
        $routeConfigurator->enableGet();
        $routeConfigurator->enablePost();
        $routeConfigurator->enablePatch();
        $routeConfigurator->enableDelete();

        if ($callback !== null) {
            $callback($routeConfigurator);
        }

        $this->route($routeConfigurator);
    }

    public function get(string $path, string $action, string $name, callable $callback = null): void
    {
        $routeConfigurator = new RouteConfigurator($name, $path, $action);
        $routeConfigurator->enableGet();

        if ($callback !== null) {
            $callback($routeConfigurator);
        }

        $this->route($routeConfigurator);
    }

    public function post(string $path, string $action, string $name, callable $callback = null): void
    {
        $routeConfigurator = new RouteConfigurator($name, $path, $action);
        $routeConfigurator->enablePost();

        if ($callback !== null) {
            $callback($routeConfigurator);
        }

        $this->route($routeConfigurator);
    }

    public function patch(string $path, string $action, string $name, callable $callback = null): void
    {
        $routeConfigurator = new RouteConfigurator($name, $path, $action);
        $routeConfigurator->enablePatch();

        if ($callback !== null) {
            $callback($routeConfigurator);
        }

        $this->route($routeConfigurator);
    }

    public function put(string $path, string $action, string $name, callable $callback = null): void
    {
        $routeConfigurator = new RouteConfigurator($name, $path, $action);
        $routeConfigurator->enablePut();

        if ($callback !== null) {
            $callback($routeConfigurator);
        }

        $this->route($routeConfigurator);
    }

    public function delete(string $path, string $action, string $name, callable $callback = null): void
    {
        $routeConfigurator = new RouteConfigurator($name, $path, $action);
        $routeConfigurator->enableDelete();

        if ($callback !== null) {
            $callback($routeConfigurator);
        }

        $this->route($routeConfigurator);
    }

    public function getRoutes(): array
    {
        $routes = [];
        if (!$this->routesQueue->isEmpty()) {
            $this->routesQueue->top();
            while ($this->routesQueue->valid()) {
                $routeConfigurator = $this->routesQueue->extract();
                $routes[] =  [
                    'name' => $routeConfigurator->getName(),
                    'path' => $routeConfigurator->getPath(),
                    'pipe' => $routeConfigurator->getPipe(),
                    'methods' => $routeConfigurator->getMethods(),
                    'options' => $routeConfigurator->getOptions(),
                ];
            }
        }

        return $routes;
    }

    public function getMiddlewarePipe(): array
    {
        foreach ($this->segments as $segment => $segmentData) {
            $this->middlewareQueue->insert([
                'type' => PipeConfig::TYPE_SEGMENT,
                'value' => [
                    'segment' => $segment,
                    'pipeConfig' => new PipeConfig($segmentData['pipeConfigurator']),
                ],
            ], $segmentData['priority']);
        }

        $pipe = [];
        if (!$this->middlewareQueue->isEmpty()) {
            $this->middlewareQueue->top();
            while ($this->middlewareQueue->valid()) {
                $pipe[] = $this->middlewareQueue->extract();
            }
        }

        return $pipe;
    }
}
