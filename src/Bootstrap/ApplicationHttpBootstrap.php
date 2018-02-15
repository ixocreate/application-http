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
namespace KiwiSuite\ApplicationHttp\Bootstrap;

use KiwiSuite\Application\Bootstrap\BootstrapInterface;
use KiwiSuite\Application\ConfiguratorItem\ConfiguratorRegistry;
use KiwiSuite\Application\ConfiguratorItem\ServiceManagerConfiguratorItem;
use KiwiSuite\Application\Service\ServiceRegistry;
use KiwiSuite\ApplicationHttp\ConfiguratorItem\MiddlewareConfiguratorItem;
use KiwiSuite\ApplicationHttp\ConfiguratorItem\PipeConfiguratorItem;
use KiwiSuite\ApplicationHttp\Factory\FastRouterFactory;
use KiwiSuite\ApplicationHttp\Factory\RequestHandlerRunnerFactory;
use KiwiSuite\ApplicationHttp\Middleware\Factory\MiddlewareSubManagerFactory;
use KiwiSuite\ApplicationHttp\Middleware\Factory\SegmentMiddlewareFactory;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\ApplicationHttp\Middleware\SegmentMiddlewarePipe;
use KiwiSuite\ServiceManager\ServiceManager;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

final class ApplicationHttpBootstrap implements BootstrapInterface
{

    /**
     * @param ConfiguratorRegistry $configuratorRegistry
     */
    public function configure(ConfiguratorRegistry $configuratorRegistry): void
    {
        /** @var ServiceManagerConfigurator $serviceManagerConfigurator */
        $serviceManagerConfigurator = $configuratorRegistry->get(ServiceManagerConfiguratorItem::class);
        $serviceManagerConfigurator->addFactory(RequestHandlerRunner::class, RequestHandlerRunnerFactory::class);
        $serviceManagerConfigurator->addFactory(FastRouteRouter::class, FastRouterFactory::class);
        $serviceManagerConfigurator->addSubManager(MiddlewareSubManager::class, MiddlewareSubManagerFactory::class);

        /** @var ServiceManagerConfigurator $middlewareConfigurator */
        $middlewareConfigurator = $configuratorRegistry->get(MiddlewareConfiguratorItem::class);
        $middlewareConfigurator->addFactory(SegmentMiddlewarePipe::class, SegmentMiddlewareFactory::class);
    }

    /**
     * @param ServiceRegistry $serviceRegistry
     */
    public function addServices(ServiceRegistry $serviceRegistry): void
    {
        // TODO: Implement addServices() method.
    }

    /**
     * @return array|null
     */
    public function getConfiguratorItems(): ?array
    {
        return [
            MiddlewareConfiguratorItem::class,
            PipeConfiguratorItem::class,
        ];
    }

    /**
     * @return array|null
     */
    public function getDefaultConfig(): ?array
    {
        return null;
    }

    /**
     * @param ServiceManager $serviceManager
     */
    public function boot(ServiceManager $serviceManager): void
    {
    }
}
