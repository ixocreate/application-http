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

use KiwiSuite\Application\ApplicationInterface;
use KiwiSuite\Application\Bootstrap;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfig;
use KiwiSuite\ServiceManager\ServiceManager;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

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

        ($serviceManager->build(RequestHandlerRunner::class, [
            PipeConfig::class => $serviceManager->get(PipeConfig::class),
        ]))->run();
    }
}
