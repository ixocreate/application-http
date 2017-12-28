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
namespace KiwiSuite\ApplicationHttp\Pipe;

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
     * PipeConfigurator constructor.
     */
    public function __construct()
    {
        $this->globalQueue = new SplPriorityQueue();
        $this->routingQueue = new SplPriorityQueue();
        $this->dispatchQueue = new SplPriorityQueue();
    }

    /**
     * @param string $middleware
     * @param int $priority
     */
    public function addGlobalMidPipe(string $middleware, int $priority = 100): void
    {
        //TODO Check Middleware
        $this->globalQueue->insert($middleware, $priority);
    }

    /**
     * @param string $middleware
     * @param int $priority
     */
    public function addRoutingPipe(string $middleware, int $priority = 100): void
    {
        //TODO Check Middleware
        $this->routingQueue->insert($middleware, $priority);
    }

    /**
     * @param string $middleware
     * @param int $priority
     */
    public function addDispatchPipe(string $middleware, int $priority = 100): void
    {
        //TODO Check Middleware
        $this->dispatchQueue->insert($middleware, $priority);
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
        return new PipeConfig(
            $globalPipe,
            $routingPipe,
            $dispatchPipe
        );
    }
}
