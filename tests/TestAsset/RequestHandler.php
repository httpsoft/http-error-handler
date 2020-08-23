<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler\TestAsset;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
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
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->code ? new Response($this->code) : new Response();
    }
}
