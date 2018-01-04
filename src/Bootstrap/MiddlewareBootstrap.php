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

use KiwiSuite\Application\ApplicationConfig;
use KiwiSuite\Application\Bootstrap\BootstrapInterface;
use KiwiSuite\Application\Bootstrap\BootstrapRegistry;
use KiwiSuite\Application\IncludeHelper;
use KiwiSuite\ApplicationHttp\Middleware\Factory\ImplicitHeadMiddlewareFactory;
use KiwiSuite\ApplicationHttp\Middleware\Factory\ImplicitOptionsMiddlewareFactory;
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareServiceManagerConfig;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;

final class MiddlewareBootstrap implements BootstrapInterface
{
    /**
     * @var string
     */
    private $bootstrapFilename = 'middleware.php';
    /**
     * @param ApplicationConfig $applicationConfig
     * @param BootstrapRegistry $bootstrapRegistry
     */
    public function bootstrap(ApplicationConfig $applicationConfig, BootstrapRegistry $bootstrapRegistry): void
    {
        $serviceManagerConfigurator = new ServiceManagerConfigurator(MiddlewareServiceManagerConfig::class);
        $this->addDefaults($serviceManagerConfigurator);

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
                    ['middlewareConfigurator' => $serviceManagerConfigurator]
                );
            }
        }
        $bootstrapRegistry->addService(MiddlewareServiceManagerConfig::class, $serviceManagerConfigurator->getServiceManagerConfig());
    }

    private function addDefaults(ServiceManagerConfigurator $serviceManagerConfigurator) : void
    {
        $serviceManagerConfigurator->addFactory(ImplicitHeadMiddleware::class, ImplicitHeadMiddlewareFactory::class);
        $serviceManagerConfigurator->addFactory(ImplicitOptionsMiddleware::class, ImplicitOptionsMiddlewareFactory::class);
    }

    public function configureServiceManager(ServiceManagerConfigurator $serviceManagerConfigurator): void
    {
    }
}
