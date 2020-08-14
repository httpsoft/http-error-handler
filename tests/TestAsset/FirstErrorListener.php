<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler\TestAsset;

use HttpSoft\ErrorHandler\ErrorListenerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class FirstErrorListener implements ErrorListenerInterface
{
    /**
     * @var bool
     */
    private bool $triggered = false;

    /**
     * @return bool
     */
    public function triggered(): bool
    {
        return $this->triggered;
    }

    /**
     * @param Throwable $error
     * @param ServerRequestInterface $request
     */
    public function trigger(Throwable $error, ServerRequestInterface $request): void
    {
        $this->triggered = true;
    }
}
