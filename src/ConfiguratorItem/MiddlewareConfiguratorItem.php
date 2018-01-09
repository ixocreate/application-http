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
use KiwiSuite\ApplicationHttp\Middleware\Factory\ImplicitHeadMiddlewareFactory;
use KiwiSuite\ApplicationHttp\Middleware\Factory\ImplicitOptionsMiddlewareFactory;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareServiceManagerConfig;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;

final class MiddlewareConfiguratorItem implements ConfiguratorItemInterface
{
    /**
     * @return mixed
     */
    public function getConfigurator()
    {
        $serviceManagerConfigurator = new ServiceManagerConfigurator(MiddlewareServiceManagerConfig::class);
        $serviceManagerConfigurator->addFactory(ImplicitHeadMiddleware::class, ImplicitHeadMiddlewareFactory::class);
        $serviceManagerConfigurator->addFactory(ImplicitOptionsMiddleware::class, ImplicitOptionsMiddlewareFactory::class);

        return $serviceManagerConfigurator;
    }

    /**
     * @return string
     */
    public function getConfiguratorName(): string
    {
        return 'middlewareConfigurator';
    }

    /**
     * @return string
     */
    public function getConfiguratorFileName(): string
    {
        return 'middleware.php';
    }

    /**
     * @param ServiceManagerConfigurator $configurator
     * @return \Serializable
     */
    public function getService($configurator): \Serializable
    {
        return $configurator->getServiceManagerConfig();
    }
}
