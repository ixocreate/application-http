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

use KiwiSuite\Application\ApplicationConfig;
use KiwiSuite\Application\Bootstrap\BootstrapInterface;
use KiwiSuite\Application\Bootstrap\BootstrapRegistry;
use KiwiSuite\Application\ConfiguratorItem\ConfiguratorItemInterface;
use KiwiSuite\Application\IncludeHelper;
use KiwiSuite\ApplicationHttp\Route\RouteConfig;
use KiwiSuite\ApplicationHttp\Route\RouteConfigurator;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;

final class RouteConfiguratorItem implements ConfiguratorItemInterface
{
    /**
     * @var string
     */
    private $bootstrapFilename = 'route.php';
    /**
     * @param ApplicationConfig $applicationConfig
     * @param BootstrapRegistry $bootstrapRegistry
     */
    public function bootstrap(ApplicationConfig $applicationConfig, BootstrapRegistry $bootstrapRegistry): void
    {
        $routeConfigurator = new RouteConfigurator();
        $bootstrapDirectories = [
            $applicationConfig->getBootstrapDirectory(),
        ];
        foreach ($bootstrapRegistry->getModules() as $module) {
            $bootstrapDirectories[] = $module->getBootstrapDirectory();
        }
        foreach ($bootstrapDirectories as $directory) {
            if (\file_exists($directory . $this->bootstrapFilename)) {
                IncludeHelper::include(
                    $directory . $this->bootstrapFilename,
                    ['routeConfigurator' => $routeConfigurator]
                );
            }
        }
        $bootstrapRegistry->addService(RouteConfig::class, $routeConfigurator->getRouteConfig());
    }

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
