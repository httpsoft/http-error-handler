<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    use ErrorHandlerTrait;

    /**
     * @param ErrorResponseGeneratorInterface|null $responseGenerator
     */
    public function __construct(ErrorResponseGeneratorInterface $responseGenerator = null)
    {
        $this->responseGenerator = $responseGenerator ?? new ErrorResponseGenerator();
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ErrorException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handleError($request, $handler);
    }
}
