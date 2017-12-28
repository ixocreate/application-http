<?php
namespace KiwiSuite\ApplicationHttp\Middleware\Factory;

use KiwiSuite\ServiceManager\FactoryInterface;
use KiwiSuite\ServiceManager\ServiceManagerInterface;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;

final class ImplicitOptionsMiddlewareFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new ImplicitOptionsMiddleware();
    }
}
