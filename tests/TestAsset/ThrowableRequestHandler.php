<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler\TestAsset;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class ThrowableRequestHandler implements RequestHandlerInterface
{
    /**
     * @var int|null
     */
    private ?int $code;

    /**
     * @param int|null $code
     */
    public function __construct(int $code = null)
    {
        $this->code = $code;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new RuntimeException('', $this->code ?? 0);
    }
}
