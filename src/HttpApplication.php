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
namespace KiwiSuite\ApplicationHttp;

use KiwiSuite\Application\ApplicationConfigurator;
use KiwiSuite\Application\ApplicationInterface;
use KiwiSuite\Application\Bootstrap;
use KiwiSuite\Application\ConfiguratorItem\ConfiguratorRegistry;
use KiwiSuite\ApplicationHttp\ConfiguratorItem\MiddlewareConfiguratorItem;
use KiwiSuite\ApplicationHttp\ConfiguratorItem\PipeConfiguratorItem;
use KiwiSuite\ApplicationHttp\ConfiguratorItem\RouteConfiguratorItem;
use KiwiSuite\ApplicationHttp\Factory\ApplicationFactory;
use KiwiSuite\ApplicationHttp\Middleware\Factory\MiddlewareSubManagerFactory;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareSubManager;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ApplicationHttp\Route\RouteConfig;
use KiwiSuite\CommonTypes\Bootstrap\CommonTypesBootstrap;
use KiwiSuite\Database\Bootstrap\DatabaseBootstrap;
use KiwiSuite\Entity\Bootstrap\EntityBootstrap;
use KiwiSuite\ProjectUri\Bootstrap\ProjectUriBootstrap;
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
        ($serviceManager->build(Application::class, [
            PipeConfig::class => $serviceManager->get(PipeConfig::class),
            RouteConfig::class => $serviceManager->get(RouteConfig::class),
        ]))->run();
    }

    /**
     * @param ApplicationConfigurator $applicationConfigurator
     * @return mixed
     */
    public function configureApplicationConfig(ApplicationConfigurator $applicationConfigurator) : void
    {
        $applicationConfigurator->addConfiguratorItem(MiddlewareConfiguratorItem::class);
        $applicationConfigurator->addConfiguratorItem(PipeConfiguratorItem::class);
        $applicationConfigurator->addConfiguratorItem(RouteConfiguratorItem::class);

        $applicationConfigurator->addBootstrapItem(EntityBootstrap::class);
        $applicationConfigurator->addBootstrapItem(CommonTypesBootstrap::class);
        $applicationConfigurator->addBootstrapItem(ProjectUriBootstrap::class);
        $applicationConfigurator->addBootstrapItem(DatabaseBootstrap::class);
    }

    /**
     * @param ConfiguratorRegistry $configuratorRegistry
     */
    public function configure(ConfiguratorRegistry $configuratorRegistry): void
    {
        /** @var ServiceManagerConfigurator $serviceManagerConfigurator */
        $serviceManagerConfigurator = $configuratorRegistry->getConfigurator('serviceManagerConfigurator');

        $serviceManagerConfigurator->addFactory(Application::class, ApplicationFactory::class);
        $serviceManagerConfigurator->addSubManager(MiddlewareSubManager::class, MiddlewareSubManagerFactory::class);
    }
}
