<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorListenerInterface
{
    /**
     * Trigger error listener.
     *
     * @param Throwable $error
     * @param ServerRequestInterface $request
     */
    public function trigger(Throwable $error, ServerRequestInterface $request): void;
}
