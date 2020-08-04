<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorResponseGeneratorInterface
{
    /**
     * Generates an instance of `Psr\Http\Message\ResponseInterface` with information about the handled error.
     *
     * @param Throwable $error
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function generate(Throwable $error, ServerRequestInterface $request): ResponseInterface;
}
