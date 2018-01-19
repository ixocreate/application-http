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
namespace KiwiSuite\ApplicationHttp\Route;

use Interop\Http\Server\MiddlewareInterface;
use KiwiSuite\Application\Exception\InvalidArgumentException;

final class RouteConfigurator
{
    /**
     * @var array
     */
    private $routes = [];

    /**
     * @param string $path
     * @param string $action
     * @param string $name
     * @param array $middlewares
     */
    public function addGet(string $path, string $action, string $name, array $middlewares = []): void
    {
        $this->addRoute($path, $action, $name, ['GET'], $middlewares);
    }

    /**
     * @param string $path
     * @param string $action
     * @param string $name
     * @param array $middlewares
     */
    public function addPost(string $path, string $action, string $name, array $middlewares = []): void
    {
        $this->addRoute($path, $action, $name, ['POST'], $middlewares);
    }

    /**
     * @param string $path
     * @param string $action
     * @param string $name
     * @param array $middlewares
     */
    public function addDelete(string $path, string $action, string $name, array $middlewares = []): void
    {
        $this->addRoute($path, $action, $name, ['DELETE'], $middlewares);
    }

    /**
     * @param string $path
     * @param string $action
     * @param string $name
     * @param array $middlewares
     */
    public function addPut(string $path, string $action, string $name, array $middlewares = []): void
    {
        $this->addRoute($path, $action, $name, ['PUT'], $middlewares);
    }

    /**
     * @param string $path
     * @param string $action
     * @param string $name
     * @param array $middlewares
     */
    public function addPatch(string $path, string $action, string $name, array $middlewares = []): void
    {
        $this->addRoute($path, $action, $name, ['PATCH'], $middlewares);
    }

    /**
     * @param string $path
     * @param array $middleware
     * @param string $name
     * @param array|null $methods
     */
    public function addRoute(string $path, string $action, string $name, array $methods = null, array $middlewares = []): void
    {
        if ($methods !== null) {
            $methods = \array_values($methods);
            foreach ($methods as $method) {
                if (!\in_array($method, ['GET', 'POST', 'DELETE', 'PUT', 'PATCH'])) {
                    throw new InvalidArgumentException("'\$methods' must be an array of valid methods (GET, POST, DELETE, PUT, PATCH)");
                }
            }
        }
        $middlewares = \array_values($middlewares);
        $middlewares[] = $action;
        $this->checkMiddlewareArray($middlewares);

        $this->routes[] = [
            'path' => $path,
            'middlewares' => $middlewares,
            'name' => $name,
            'methods' => $methods,
        ];
    }

    /**
     * @param string $middleware
     * @return bool
     */
    private function checkMiddlewareString(string $middleware) : void
    {
        $implements = class_implements($middleware);
        if (!\in_array(MiddlewareInterface::class, $implements)) {
            //TODO Exception
            throw new \InvalidArgumentException(sprintf("'%s' must implement '%s'", $middleware, MiddlewareInterface::class));
        }
    }

    /**
     * @param array $middlewares
     */
    private function checkMiddlewareArray(array $middlewares) : void
    {
        foreach ($middlewares as $middleware) {
            $this->checkMiddlewareString($middleware);
        }
    }

    /**
     * @return RouteConfig
     */
    public function getRouteConfig(): RouteConfig
    {
        return new RouteConfig($this->routes);
    }
}
