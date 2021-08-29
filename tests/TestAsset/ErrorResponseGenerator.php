<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler\TestAsset;

use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorResponseGenerator implements ErrorResponseGeneratorInterface
{
    /**
     * @var int
     */
    private int $code;

    /**
     * @param int $code
     */
    public function __construct(int $code)
    {
        $this->code = $code;
    }

    public function generate(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        return new Response($this->code);
    }
}
