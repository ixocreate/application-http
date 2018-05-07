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

namespace KiwiSuite\ApplicationHttp\ErrorHandling\Factory;

use KiwiSuite\ApplicationHttp\ErrorHandling\Response\NotFoundHandler;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Template\Renderer;
use Zend\Diactoros\Response;
use KiwiSuite\Config\Config;

final class NotFoundHandlerFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return NotFoundHandler|mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get(Config::class)->get('error');

        $renderer = $container->has(Renderer::class)
            ? $container->get(Renderer::class)
            : null;

        $template = isset($config['template_404'])
            ? $config['template_404']
            : NotFoundHandler::TEMPLATE_DEFAULT;

        return new NotFoundHandler(
            function () {
                return new Response();
            },
            $renderer,
            $template
        );
    }
}
