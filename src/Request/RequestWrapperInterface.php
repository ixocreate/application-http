<?php
namespace KiwiSuite\ApplicationHttp\Request;

use Psr\Http\Message\ServerRequestInterface;

interface RequestWrapperInterface
{
    /**
     * @return ServerRequestInterface
     */
    public function previousRequest(): ServerRequestInterface;

    /**
     * @return ServerRequestInterface
     */
    public function rootRequest(): ServerRequestInterface;
}
