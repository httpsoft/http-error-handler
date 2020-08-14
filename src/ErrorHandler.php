<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ErrorHandler implements RequestHandlerInterface
{
    use ErrorHandlerTrait;

    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $handler;

    /**
     * @var ErrorResponseGeneratorInterface
     */
    private ErrorResponseGeneratorInterface $responseGenerator;

    /**
     * @param RequestHandlerInterface $handler
     * @param ErrorResponseGeneratorInterface|null $responseGenerator
     */
    public function __construct(
        RequestHandlerInterface $handler,
        ErrorResponseGeneratorInterface $responseGenerator = null
    ) {
        $this->handler = $handler;
        $this->responseGenerator = $responseGenerator ?? new ErrorResponseGenerator();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ErrorException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handleError($request, $this->handler);
    }
}
