<?php
namespace Ixocreate\ApplicationHttp\Pipe\Config;

final class MiddlewareConfig implements \Serializable
{
    /**
     * @var string
     */
    private $middleware;

    /**
     * MiddlewareConfig constructor.
     * @param string $middleware
     */
    public function __construct(string $middleware)
    {

        $this->middleware = $middleware;
    }

    /**
     * @return string
     */
    public function middleware(): string
    {
        return $this->middleware;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->middleware);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->middleware = unserialize($serialized);
    }
}
