<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler\TestAsset;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class ErrorRequestHandler implements RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $array = [];
        $array['undefined'];
        return new Response();
    }
}
