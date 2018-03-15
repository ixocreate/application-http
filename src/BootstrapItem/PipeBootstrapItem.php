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
namespace KiwiSuite\ApplicationHttp\BootstrapItem;

use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\Contract\Application\BootstrapItemInterface;
use KiwiSuite\Contract\Application\ConfiguratorInterface;

final class PipeBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new PipeConfigurator();
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'pipe';
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'pipe.php';
    }
}