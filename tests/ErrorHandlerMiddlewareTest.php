<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler;

use HttpSoft\ErrorHandler\ErrorHandlerMiddleware;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\ServerRequest\ServerRequestCreator;
use HttpSoft\Response\ResponseStatusCodeInterface;
use HttpSoft\Tests\ErrorHandler\TestAsset\ErrorRequestHandler;
use HttpSoft\Tests\ErrorHandler\TestAsset\ThrowableRequestHandler;
use HttpSoft\Tests\ErrorHandler\TestAsset\FirstErrorListener;
use HttpSoft\Tests\ErrorHandler\TestAsset\RequestHandler;
use HttpSoft\Tests\ErrorHandler\TestAsset\SecondErrorListener;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function error_reporting;

use const E_ERROR;
use const E_NOTICE;
use const E_WARNING;

class ErrorHandlerMiddlewareTest extends TestCase implements ResponseStatusCodeInterface
{
    private const PHRASES = ErrorResponseGeneratorInterface::ERROR_PHRASES;

    /**
     * @var int
     */
    private int $errorReporting = 0;

    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var ErrorHandlerMiddleware
     */
    private ErrorHandlerMiddleware $errorHandler;

    public function setUp(): void
    {
        $this->errorHandler = new ErrorHandlerMiddleware();
        $this->request = ServerRequestCreator::create();
        $this->errorReporting = error_reporting();
    }

    public function tearDown(): void
    {
        error_reporting($this->errorReporting);
    }

    public function testWithRequestHandlerAndWithStatusOk(): void
    {
        $response = $this->errorHandler->process($this->request, $this->createRequestHandler());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testWithRequestHandlerAndWithStatusCreated(): void
    {
        $response = $this->errorHandler->process($this->request, $this->createRequestHandler(self::STATUS_CREATED));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_CREATED, $response->getStatusCode());
        $this->assertSame('Created', $response->getReasonPhrase());
    }

    public function testWithRequestHandlerAndWithErrorResponseGeneratorMock(): void
    {
        $errorHandler = new ErrorHandlerMiddleware($this->createMock(ErrorResponseGeneratorInterface::class));
        $response = $errorHandler->process($this->request, $this->createRequestHandler());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testWithThrowableRequestHandlerAndWithDefaultError(): void
    {
        $response = $this->errorHandler->process($this->request, $this->createThrowableRequestHandler());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testWithThrowableRequestHandlerAndWithDefaultErrorIfNotValidStatusCode(): void
    {
        $response = $this->errorHandler->process(
            $this->request,
            $this->createThrowableRequestHandler(self::STATUS_MOVED_PERMANENTLY)
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testWithThrowableRequestHandlerAndWithNotFoundError(): void
    {
        $response = $this->errorHandler->process(
            $this->request,
            $this->createThrowableRequestHandler(self::STATUS_NOT_FOUND)
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_NOT_FOUND], $response->getReasonPhrase());
    }

    public function testWithErrorRequestHandlerAndWithoutCaughtError(): void
    {
        $response = $this->errorHandler->process($this->request, $this->createErrorRequestHandler(E_ERROR));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testWithErrorRequestHandlerAndWithCaughtError(): void
    {
        $response = $this->errorHandler->process(
            $this->request,
            $this->createErrorRequestHandler(E_NOTICE | E_WARNING)
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testWithRequestHandlerAndWithAdditionOfListeners(): void
    {
        $this->errorHandler->addListener($firstListener = new FirstErrorListener());
        $this->errorHandler->addListener($secondListener = new SecondErrorListener());
        $response = $this->errorHandler->process($this->request, $this->createRequestHandler());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());

        $this->assertFalse($firstListener->triggered());
        $this->assertFalse($secondListener->triggered());
    }

    public function testWithThrowableRequestHandlerAndWithAdditionOfListeners(): void
    {
        $this->errorHandler->addListener($firstListener = new FirstErrorListener());
        $this->errorHandler->addListener($secondListener = new SecondErrorListener());
        $response = $this->errorHandler->process($this->request, $this->createThrowableRequestHandler());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());

        $this->assertTrue($firstListener->triggered());
        $this->assertTrue($secondListener->triggered());
    }

    public function testWithErrorRequestHandlerAndWithAdditionOfListeners(): void
    {
        $this->errorHandler->addListener($firstListener = new FirstErrorListener());
        $this->errorHandler->addListener($secondListener = new SecondErrorListener());
        $response = $this->errorHandler->process(
            $this->request,
            $this->createErrorRequestHandler(E_NOTICE | E_WARNING)
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());

        $this->assertTrue($firstListener->triggered());
        $this->assertTrue($secondListener->triggered());
    }

    /**
     * @param int|null $code
     * @return RequestHandler
     */
    private function createRequestHandler(int $code = null): RequestHandler
    {
        return new RequestHandler($code);
    }

    /**
     * @param int|null $code
     * @return ThrowableRequestHandler
     */
    private function createThrowableRequestHandler(int $code = null): ThrowableRequestHandler
    {
        return new ThrowableRequestHandler($code);
    }

    /**
     * @param int $level
     * @return ErrorRequestHandler
     */
    private function createErrorRequestHandler(int $level): ErrorRequestHandler
    {
        error_reporting($level);
        return new ErrorRequestHandler();
    }
}
