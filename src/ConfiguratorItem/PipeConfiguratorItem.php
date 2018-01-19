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
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\ProjectUri\Middleware\ProjectUriCheckMiddleware;
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
        $pipeConfigurator->addGlobalMiddleware(ProjectUriCheckMiddleware::class, 10000);
        $pipeConfigurator->addRoutingMiddleware(ImplicitHeadMiddleware::class);
        $pipeConfigurator->addRoutingMiddleware(ImplicitOptionsMiddleware::class);

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
