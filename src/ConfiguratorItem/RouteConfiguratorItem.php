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
namespace KiwiSuite\ApplicationHttp\ConfiguratorItem;

use KiwiSuite\Application\ConfiguratorItem\ConfiguratorItemInterface;
use KiwiSuite\ApplicationHttp\Route\RouteConfigurator;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;

final class RouteConfiguratorItem implements ConfiguratorItemInterface
{
    public function configureServiceManager(ServiceManagerConfigurator $serviceManagerConfigurator): void
    {
    }

    /**
     * @return mixed
     */
    public function getConfigurator()
    {
        return new RouteConfigurator();
    }

    /**
     * @return string
     */
    public function getConfiguratorName(): string
    {
        return 'routeConfigurator';
    }

    /**
     * @return string
     */
    public function getConfiguratorFileName(): string
    {
        return 'route.php';
    }

    /**
     * @param RouteConfigurator $configurator
     * @return \Serializable
     */
    public function getService($configurator): \Serializable
    {
        return $configurator->getRouteConfig();
    }
}
