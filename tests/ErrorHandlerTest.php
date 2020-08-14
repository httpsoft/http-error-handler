<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler;

use HttpSoft\ErrorHandler\ErrorHandler;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\Request\ServerRequestFactory;
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

class ErrorHandlerTest extends TestCase implements ResponseStatusCodeInterface
{
    /**
     * @var int
     */
    private int $errorReporting = 0;

    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    public function setUp(): void
    {
        $this->request = ServerRequestFactory::create();
        $this->errorReporting = error_reporting();
    }

    public function tearDown(): void
    {
        error_reporting($this->errorReporting);
    }

    public function testWithRequestHandlerAndWithStatusOk(): void
    {
        $errorHandler = $this->createWithRequestHandler();
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_OK], $response->getReasonPhrase());
    }

    public function testWithRequestHandlerAndWithStatusCreated(): void
    {
        $errorHandler = $this->createWithRequestHandler(self::STATUS_CREATED);
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_CREATED, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_CREATED], $response->getReasonPhrase());
    }

    public function testWithRequestHandlerAndWithErrorResponseGeneratorMock(): void
    {
        $errorResponseGenerator = $this->createMock(ErrorResponseGeneratorInterface::class);
        $errorHandler = new ErrorHandler(new RequestHandler(), $errorResponseGenerator);
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_OK], $response->getReasonPhrase());
    }

    public function testWithThrowableRequestHandlerAndWithDefaultError(): void
    {
        $errorHandler = $this->createWithThrowableRequestHandler();
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testWithThrowableRequestHandlerAndWithDefaultErrorIfNotValidStatusCode(): void
    {
        $errorHandler = $this->createWithThrowableRequestHandler(self::STATUS_MOVED_PERMANENTLY);
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testWithThrowableRequestHandlerAndWithNotFoundError(): void
    {
        $errorHandler = $this->createWithThrowableRequestHandler(self::STATUS_NOT_FOUND);
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_NOT_FOUND], $response->getReasonPhrase());
    }

    public function testWithErrorRequestHandlerAndWithoutCaughtError(): void
    {
        $errorHandler = $this->createWithErrorRequestHandler(E_ERROR);
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_OK], $response->getReasonPhrase());
    }

    public function testWithErrorRequestHandlerAndWithCaughtError(): void
    {
        $errorHandler = $this->createWithErrorRequestHandler(E_NOTICE);
        $response = $errorHandler->handle($this->request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testWithRequestHandlerAndWithAdditionOfListeners(): void
    {
        $errorHandler = $this->createWithRequestHandler();
        $errorHandler->addListener($firstListener = new FirstErrorListener());
        $errorHandler->addListener($secondListener = new SecondErrorListener());
        $response = $errorHandler->handle($this->request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_OK, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_OK], $response->getReasonPhrase());

        $this->assertFalse($firstListener->triggered());
        $this->assertFalse($secondListener->triggered());
    }

    public function testWithThrowableRequestHandlerAndWithAdditionOfListeners(): void
    {
        $errorHandler = $this->createWithThrowableRequestHandler();
        $errorHandler->addListener($firstListener = new FirstErrorListener());
        $errorHandler->addListener($secondListener = new SecondErrorListener());
        $response = $errorHandler->handle($this->request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());

        $this->assertTrue($firstListener->triggered());
        $this->assertTrue($secondListener->triggered());
    }

    public function testWithErrorRequestHandlerAndWithAdditionOfListeners(): void
    {
        $errorHandler = $this->createWithErrorRequestHandler(E_NOTICE);
        $errorHandler->addListener($firstListener = new FirstErrorListener());
        $errorHandler->addListener($secondListener = new SecondErrorListener());
        $response = $errorHandler->handle($this->request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());

        $this->assertTrue($firstListener->triggered());
        $this->assertTrue($secondListener->triggered());
    }

    /**
     * @param int|null $code
     * @return ErrorHandler
     */
    private function createWithRequestHandler(int $code = null): ErrorHandler
    {
        return new ErrorHandler(new RequestHandler($code));
    }

    /**
     * @param int|null $code
     * @return ErrorHandler
     */
    private function createWithThrowableRequestHandler(int $code = null): ErrorHandler
    {
        return new ErrorHandler(new ThrowableRequestHandler($code));
    }

    /**
     * @param int $level
     * @return ErrorHandler
     */
    private function createWithErrorRequestHandler(int $level): ErrorHandler
    {
        error_reporting($level);
        return new ErrorHandler(new ErrorRequestHandler());
    }
}
