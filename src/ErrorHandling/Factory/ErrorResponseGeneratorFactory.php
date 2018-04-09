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

use KiwiSuite\ApplicationHttp\ErrorHandling\Response\ErrorResponseGenerator;
use KiwiSuite\Application\ApplicationConfig;
use KiwiSuite\Config\Config;
use KiwiSuite\Contract\ServiceManager\FactoryInterface;
use KiwiSuite\Contract\ServiceManager\ServiceManagerInterface;
use KiwiSuite\Template\Renderer;
use Zend\Expressive\Middleware\WhoopsErrorResponseGenerator;

final class ErrorResponseGeneratorFactory implements FactoryInterface
{
    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ErrorResponseGenerator|mixed|WhoopsErrorResponseGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        $develop = $container->get(ApplicationConfig::class)->isDevelopment();

        $config = $container->get(Config::class)->get('error');

        $renderer = $container->has(Renderer::class)
            ? $container->get(Renderer::class)
            : null;

        if ($develop === true) {
            return new WhoopsErrorResponseGenerator((new WhoopsFactory())($container, $requestedName, $options));
        }
        $template = isset($config['template_error'])
            ? $config['template_error']
            : ErrorResponseGenerator::TEMPLATE_DEFAULT;

        return new ErrorResponseGenerator($renderer, $template);
    }
}