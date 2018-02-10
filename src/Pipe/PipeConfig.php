<?php
declare(strict_types=1);
namespace KiwiSuite\ApplicationHttp\Pipe;

class PipeConfig implements \Serializable
{
    public const TYPE_PIPE = "pipe";
    public const TYPE_SEGMENT = "segment";
    public const TYPE_ROUTING = "routing";
    public const TYPE_DISPATCHING = "dispatching";

    private $routes = [];

    private $middlewarePipe = [];

    public function __construct(PipeConfigurator $pipeConfigurator)
    {
        $this->routes = $pipeConfigurator->getRoutes();
        $this->middlewarePipe = $pipeConfigurator->getMiddlewarePipe();
    }

    final public function getRoutes(): array
    {
        return $this->routes;
    }

    final public function getMiddlewarePipe(): array
    {
        return $this->middlewarePipe;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize([
            'routes' => $this->routes,
            'middlewarePipe' => $this->middlewarePipe,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->routes = [];
        $this->middlewarePipe = [];

        $array = \unserialize($serialized);

        if (!\is_array($array)) {
            return;
        }

        if (!empty($array['routes']) && \is_array($array['routes'])) {
            $this->routes = $array['routes'];
        }

        if (!empty($array['middlewarePipe']) && \is_array($array['middlewarePipe'])) {
            $this->routes = $array['middlewarePipe'];
        }
    }
}
