<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function error_reporting;
use function get_class;
use function restore_error_handler;
use function set_error_handler;

trait ErrorHandlerTrait
{
    /**
     * @var ErrorListenerInterface[]
     */
    private array $listeners = [];

    /**
     * @var ErrorResponseGeneratorInterface
     */
    private ErrorResponseGeneratorInterface $responseGenerator;

    /**
     * Adds an error listener to the queue.
     *
     * @param ErrorListenerInterface $listener
     */
    public function addListener(ErrorListenerInterface $listener): void
    {
        $this->listeners[get_class($listener)] = $listener;
    }

    /**
     * Handles errors and exceptions in the layers it wraps.
     *
     * When an exception is intercepted, a response with error information is generated and returned;
     * otherwise, the response returned by `Psr\Http\Server\RequestHandlerInterface` instance is used.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ErrorException
     */
    private function handleError(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            // https://www.php.net/manual/en/function.error-reporting.php#8866
            // Usages the defined levels of `error_reporting()`.
            if (!(error_reporting() & $severity)) {
                // This error code is not included in `error_reporting()`.
                return true;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $response = $handler->handle($request);
        } catch (Throwable $error) {
            $this->triggerListeners($error, $request);
            $response = $this->generateResponse($error, $request);
        }

        restore_error_handler();
        return $response;
    }

    /**
     * Trigger all error listeners.
     *
     * @param Throwable $error
     * @param ServerRequestInterface $request
     */
    private function triggerListeners(Throwable $error, ServerRequestInterface $request): void
    {
        foreach ($this->listeners as $listener) {
            $listener->trigger($error, $request);
        }
    }

    /**
     * Returns a response with a valid status code.
     *
     * If the generated response has a valid status code, it will be returned unchanged.
     *
     * If the status code of the generated response is invalid, but the error
     * code is valid, the response code will be changed to an error code.
     *
     * If the status code of the generated response and error code are
     * not valid, a response with the status code 500 is returned.
     *
     * @see isValidResponseCode()
     * @param Throwable $error
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    private function generateResponse(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseGenerator->generate($error, $request);

        if ($this->isValidResponseCode((int) $response->getStatusCode())) {
            return $response;
        }

        if ($this->isValidResponseCode((int) $error->getCode())) {
            return $response->withStatus((int) $error->getCode());
        }

        return $response->withStatus(500);
    }

    /**
     * Checks whether the response status code is valid or not.
     *
     * The valid response status code must be 4xx (client errors) or 5xx (server errors).
     *
     * @param int $responseCode
     * @return bool
     */
    private function isValidResponseCode(int $responseCode): bool
    {
        return ($responseCode >= 400 && $responseCode < 600);
    }
}
