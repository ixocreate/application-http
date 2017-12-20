<?php
/**
 * kiwi-suite/application-http (https://github.com/kiwi-suite/application-http)
 *
 * @package kiwi-suite/application-http
 * @see https://github.com/kiwi-suite/application-http
 * @copyright Copyright (c) 2010 - 2017 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);
namespace KiwiSuite\ApplicationHttp;

use KiwiSuite\Application\ApplicationConfigurator;
use KiwiSuite\Application\ApplicationInterface;
use KiwiSuite\Application\Bootstrap;
use KiwiSuite\ApplicationHttp\Bootstrap\MiddlewareBootstrap;
use KiwiSuite\ApplicationHttp\Bootstrap\PipeBootstrap;
use KiwiSuite\ApplicationHttp\Bootstrap\RouteBootstrap;
use KiwiSuite\ApplicationHttp\Factory\ApplicationFactory;
use KiwiSuite\ApplicationHttp\Middleware\Factory\MiddlewareSubManagerFactory;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\Config\Bootstrap\ConfigBootstrap;
use KiwiSuite\ServiceManager\ServiceManager;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use Zend\Expressive\Application;

final class HttpApplication implements ApplicationInterface
{
    /**
     * @var string
     */
    private $bootstrapDirectory;

    /**
     * ConsoleApplication constructor.
     * @param string $bootstrapDirectory
     */
    public function __construct(string $bootstrapDirectory)
    {
        $this->bootstrapDirectory = $bootstrapDirectory;
    }

    /**
     *
     */
    public function run(): void
    {
        /** @var ServiceManager $serviceManager */
        $serviceManager = (new Bootstrap())->bootstrap($this->bootstrapDirectory, $this);
        ($serviceManager->build(Application::class))->run();
    }

    /**
     * @param ApplicationConfigurator $applicationConfigurator
     * @return mixed
     */
    public function configureApplicationConfig(ApplicationConfigurator $applicationConfigurator)
    {
        $applicationConfigurator->addBootstrapItem(ConfigBootstrap::class, 100);
        $applicationConfigurator->addBootstrapItem(MiddlewareBootstrap::class, 200);
        $applicationConfigurator->addBootstrapItem(PipeBootstrap::class, 300);
        $applicationConfigurator->addBootstrapItem(RouteBootstrap::class, 400);
    }

    /**
     * @param ServiceManagerConfigurator $serviceManagerConfigurator
     */
    public function configureServiceManager(ServiceManagerConfigurator $serviceManagerConfigurator): void
    {
        $serviceManagerConfigurator->addSubManager(MiddlewareSubManager::class, MiddlewareSubManagerFactory::class);
        $serviceManagerConfigurator->addFactory(Application::class, ApplicationFactory::class);
    }
}
