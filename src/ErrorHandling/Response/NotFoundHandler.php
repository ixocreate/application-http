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

namespace KiwiSuite\ApplicationHttp\ErrorHandling\Response;

use Fig\Http\Message\StatusCodeInterface;
use KiwiSuite\Template\Renderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundHandler implements MiddlewareInterface
{
    public const TEMPLATE_DEFAULT = 'ErrorHandling::404';

    private $renderer;

    /**
     * @var callable
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $template;

    /**
     * NotFoundHandler constructor.
     * @param callable $responseFactory
     * @param Renderer|null $renderer
     * @param string $template
     */
    public function __construct(
        callable $responseFactory,
        Renderer $renderer = null,
        string $template = self::TEMPLATE_DEFAULT
    ) {
        // Factory cast to closure in order to provide return type safety.
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
        $this->renderer = $renderer;
        $this->template = $template;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->renderer === null) {
            return $this->generateNotFoundPlainResponse($request);
        }

        return $this->generateTemplateResponse($this->renderer, $request);
    }

    /**
     * Generates a plain text response indicating the request method and URI.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function generateNotFoundPlainResponse(ServerRequestInterface $request) : ResponseInterface
    {
        $response = ($this->responseFactory)()->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        $response->getBody()
            ->write(\sprintf(
                "Encountered a 404 Error, %s doesn't exist",
                (string) $request->getUri()
            ));

        return $response;
    }

    /**
     * Generates a response using a template.
     *
     * Template will receive the current request via the "request" variable.
     * @param Renderer $renderer
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function generateTemplateResponse(
        Renderer $renderer,
        ServerRequestInterface $request
    ) : ResponseInterface {

        $response = ($this->responseFactory)()->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        $response->getBody()->write(
            $renderer->render($this->template, ['request' => $request])
        );

        return $response;
    }
}