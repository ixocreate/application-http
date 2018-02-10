<?php
declare(strict_types=1);
namespace KiwiSuite\ApplicationHttp\Pipe;

final class GroupPipeConfigurator
{
    /**
     * @var array
     */
    private $before = [];

    /**
     * @var array
     */
    private $after = [];

    /**
     * @var array
     */
    private $routes = [];

    public function before(string $middleware, bool $prepend = false): void
    {
        //TODO check MiddlewareInterface

        if ($prepend === true) {
            array_unshift($this->before, $middleware);
            return;
        }

        $this->before[] = $middleware;
    }

    public function after(string $middleware, bool $prepend = false): void
    {
        //TODO check MiddlewareInterface|HandlerInterface
        if ($prepend === true) {
            array_unshift($this->after, $middleware);
            return;
        }

        $this->after[] = $middleware;
    }

    /**
     * @return array
     */
    public function getBefore(): array
    {
        return $this->before;
    }

    /**
     * @return array
     */
    public function getAfter(): array
    {
        return $this->after;
    }

    public function route(RouteConfigurator $routeConfigurator): void
    {
        $this->routes[] = $routeConfigurator;
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
        return $this->routes;
    }
}
