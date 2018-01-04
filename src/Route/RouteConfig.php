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

final class RouteConfig
{
    /**
     * @var array
     */
    private $routes = [];
    /**
     * RouteConfig constructor.
     * @param array $routes
     */
    public function __construct(array $routes)
    {
        //TODO Checks
        $this->routes = $routes;
    }
    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
