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

class PipeConfig implements \Serializable
{
    /**
     * @var array
     */
    private $globalPipe;
    /**
     * @var array
     */
    private $routingPipe;
    /**
     * @var array
     */
    private $dispatchPipe;

    /**
     * PipeConfig constructor.
     * @param array $globalPipe
     * @param array $routingPipe
     * @param array $dispatchPipe
     */
    public function __construct(array $globalPipe, array $routingPipe, array $dispatchPipe)
    {
        $this->globalPipe = $globalPipe;
        $this->routingPipe = $routingPipe;
        $this->dispatchPipe = $dispatchPipe;
    }

    /**
     * @return array
     */
    public function getGlobalPipe(): array
    {
        return $this->globalPipe;
    }

    /**
     * @return array
     */
    public function getRoutingPipe(): array
    {
        return $this->routingPipe;
    }

    /**
     * @return array
     */
    public function getDispatchPipe(): array
    {
        return $this->dispatchPipe;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'globalPipe' => $this->globalPipe,
            'routingPipe' => $this->routingPipe,
            'dispatchPipe' => $this->dispatchPipe,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);

        $this->globalPipe = $array['globalPipe'];
        $this->routingPipe = $array['routingPipe'];
        $this->dispatchPipe = $array['dispatchPipe'];
    }
}
