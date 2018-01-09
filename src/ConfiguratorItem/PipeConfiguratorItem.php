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
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;

final class PipeConfiguratorItem implements ConfiguratorItemInterface
{
    /**
     * @return mixed
     */
    public function getConfigurator()
    {
        $pipeConfigurator = new PipeConfigurator();
        $pipeConfigurator->addRoutingPipe(ImplicitHeadMiddleware::class);
        $pipeConfigurator->addRoutingPipe(ImplicitOptionsMiddleware::class);

        return $pipeConfigurator;
    }

    /**
     * @return string
     */
    public function getConfiguratorName(): string
    {
        return 'pipeConfigurator';
    }

    /**
     * @return string
     */
    public function getConfiguratorFileName(): string
    {
        return 'pipe.php';
    }

    /**
     * @param PipeConfigurator$configurator
     * @return \Serializable
     */
    public function getService($configurator): \Serializable
    {
        return $configurator->getPipeConfig();
    }
}
