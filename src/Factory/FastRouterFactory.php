<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\ApplicationHttp\Factory;

use Ixocreate\Contract\ServiceManager\FactoryInterface;
use Ixocreate\Contract\ServiceManager\ServiceManagerInterface;
use Zend\Expressive\Router\FastRouteRouter;

final class FastRouterFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FastRouteRouter
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new FastRouteRouter();
    }
}
