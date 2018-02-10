<?php
declare(strict_types=1);
namespace KiwiSuite\ApplicationHttp\Pipe;

final class RouteConfigurator
{
    private $name;

    private $path;

    /**
     * @var array
     */
    private $methods = [
        'GET' => false,
        'POST' => false,
        'PUT' => false,
        'DELETE' => false,
        'PATCH' => false,
    ];

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $before = [];

    /**
     * @var array
     */
    private $after = [];

    public function __construct(string $name, string $path, string $action)
    {
        $this->name = $name;
        $this->path = $path;
        //TODO check MiddlewareInterface|HandlerInterface
        $this->action = $action;
    }

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

    public function enableGet(): void
    {
        $this->methods['GET'] = true;
    }

    public function disableGet(): void
    {
        $this->methods['GET'] = false;
    }

    public function enablePost(): void
    {
        $this->methods['POST'] = true;
    }

    public function disablePost(): void
    {
        $this->methods['POST'] = false;
    }

    public function enablePut(): void
    {
        $this->methods['PUT'] = true;
    }

    public function disablePut(): void
    {
        $this->methods['PUT'] = false;
    }

    public function enableDelete(): void
    {
        $this->methods['DELETE'] = true;
    }

    public function disableDelete(): void
    {
        $this->methods['DELETE'] = false;
    }

    public function enablePatch(): void
    {
        $this->methods['PATCH'] = true;
    }

    public function disablePatch(): void
    {
        $this->methods['PATCH'] = false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethods(): array
    {
        return \array_keys(\array_filter($this->methods));
    }

    public function getPipe(): array
    {
        return \array_merge($this->before, [$this->action], $this->after);
    }
}
