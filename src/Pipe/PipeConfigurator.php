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

use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\UriInterface;
use \SplPriorityQueue;

final class PipeConfigurator
{
    /**
     * @var SplPriorityQueue
     */
    private $globalQueue;

    /**
     * @var SplPriorityQueue
     */
    private $routingQueue;

    /**
     * @var SplPriorityQueue
     */
    private $dispatchQueue;
    /**
     * @var string
     */
    private $pipeClass;

    /**
     * PipeConfigurator constructor.
     * @param string $pipeClass
     */
    public function __construct(string $pipeClass = PipeConfig::class)
    {
        $this->globalQueue = new SplPriorityQueue();
        $this->routingQueue = new SplPriorityQueue();
        $this->dispatchQueue = new SplPriorityQueue();
        $this->pipeClass = $pipeClass;
    }

    public function addPathMiddleware(string $path, string $middleware, int $priority = 100) : void
    {
        $this->checkMiddlewareString($middleware);
        $this->globalQueue->insert([
            'middlewares' => [$middleware],
            'path' => $path,
            'uri' => null,
        ], $priority);
    }

    public function addPathMiddlewarePipe(string $path, array $middlewares, int $priority = 100) : void
    {
        $this->checkMiddlewareArray($middlewares);
        $this->globalQueue->insert([
            'middlewares' => \array_values($middlewares),
            'path' => $path,
            'uri' => null,
        ], $priority);
    }

    /*public function addUriMiddleware(UriInterface $uri, string $middleware, int $priority = 100) : void
    {
        $this->checkMiddlewareString($middleware);
        $this->globalQueue->insert([
            'middlewares' => [$middleware],
            'path' => null,
            'uri' => $uri,
        ], $priority);
    }

    public function addUriMiddlewarePipe(UriInterface $uri, array $middlewares, int $priority = 100) : void
    {
        $this->checkMiddlewareArray($middlewares);
        $this->globalQueue->insert([
            'middlewares' => \array_values($middlewares),
            'path' => null,
            'uri' => $uri,
        ], $priority);
    }*/

    public function addGlobalMiddleware(string $middleware, int $priority = 100) : void
    {
        $this->checkMiddlewareString($middleware);
        $this->globalQueue->insert([
            'middlewares' => [$middleware],
            'path' => null,
            'uri' => null,
        ], $priority);
    }

    public function addGlobalMiddlewarePipe(array $middlewares, int $priority = 100) : void
    {
        $this->checkMiddlewareArray($middlewares);
        $this->globalQueue->insert([
            'middlewares' => \array_values($middlewares),
            'path' => null,
            'uri' => null,
        ], $priority);
    }

    public function addRoutingMiddleware(string $middleware, int $priority = 100) : void
    {
        $this->checkMiddlewareString($middleware);
        $this->routingQueue->insert([$middleware], $priority);
    }

    public function addRoutingMiddlewarePipe(array $middlewares, int $priority = 100) : void
    {
        $this->checkMiddlewareArray($middlewares);
        $this->routingQueue->insert(\array_values($middlewares), $priority);
    }

    public function addDispatchMiddleware(string $middleware, int $priority = 100) : void
    {
        $this->checkMiddlewareString($middleware);
        $this->dispatchQueue->insert([$middleware], $priority);
    }

    public function addDispatchMiddlewarePipe(array $middlewares, int $priority = 100) : void
    {
        $this->checkMiddlewareArray($middlewares);
        $this->dispatchQueue->insert(\array_values($middlewares), $priority);
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

    public function getPipeConfig(): PipeConfig
    {
        $globalPipe = [];
        if (!$this->globalQueue->isEmpty()) {
            $this->globalQueue->top();
            while ($this->globalQueue->valid()) {
                $globalPipe[] = $this->globalQueue->extract();
            }
        }
        $routingPipe = [];
        if (!$this->routingQueue->isEmpty()) {
            $this->routingQueue->top();
            while ($this->routingQueue->valid()) {
                $routingPipe[] = $this->routingQueue->extract();
            }
        }
        $dispatchPipe = [];
        if (!$this->dispatchQueue->isEmpty()) {
            $this->dispatchQueue->top();
            while ($this->dispatchQueue->valid()) {
                $dispatchPipe[] = $this->dispatchQueue->extract();
            }
        }

        return new $this->pipeClass(
            $globalPipe,
            $routingPipe,
            $dispatchPipe
        );
    }
}
