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
namespace KiwiSuite\ApplicationHttp\Bootstrap;

use KiwiSuite\Application\ApplicationConfig;
use KiwiSuite\Application\Bootstrap\BootstrapInterface;
use KiwiSuite\Application\Bootstrap\BootstrapRegistry;
use KiwiSuite\Application\IncludeHelper;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\ServiceManager\ServiceManagerConfigurator;

final class PipeBootstrap implements BootstrapInterface
{
    /**
     * @var string
     */
    private $bootstrapFilename = 'pipe.php';

    /**
     * @param ApplicationConfig $applicationConfig
     * @param BootstrapRegistry $bootstrapRegistry
     */
    public function bootstrap(ApplicationConfig $applicationConfig, BootstrapRegistry $bootstrapRegistry): void
    {
        $pipeConfigurator = new PipeConfigurator();
        $this->addDefaults($pipeConfigurator);
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
                    ['pipeConfigurator' => $pipeConfigurator]
                );
            }
        }
        $bootstrapRegistry->addService(PipeConfig::class, $pipeConfigurator->getPipeConfig());
    }

    /**
     * @param PipeConfigurator $pipeConfigurator
     */
    private function addDefaults(PipeConfigurator $pipeConfigurator) : void
    {

    }

    /**
     * @param ServiceManagerConfigurator $serviceManagerConfigurator
     */
    public function configureServiceManager(ServiceManagerConfigurator $serviceManagerConfigurator): void
    {

    }
}
