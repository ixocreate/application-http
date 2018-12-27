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
namespace Ixocreate\ApplicationHttp\BootstrapItem;

use Ixocreate\ApplicationHttp\Middleware\MiddlewareConfigurator;
use Ixocreate\Contract\Application\BootstrapItemInterface;
use Ixocreate\Contract\Application\ConfiguratorInterface;

final class MiddlewareBootstrapItem implements BootstrapItemInterface
{
    /**
     * @return ConfiguratorInterface
     */
    public function getConfigurator(): ConfiguratorInterface
    {
        return new MiddlewareConfigurator();
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return 'middleware';
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return 'middleware.php';
    }
}
