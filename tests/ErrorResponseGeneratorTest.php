<?php

declare(strict_types=1);

namespace HttpSoft\Tests\ErrorHandler;

use Exception;
use HttpSoft\ErrorHandler\ErrorResponseGenerator;
use HttpSoft\Request\ServerRequest;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\ResponseStatusCodeInterface;
use HttpSoft\Response\TextResponse;
use HttpSoft\Response\XmlResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorResponseGeneratorTest extends TestCase implements ResponseStatusCodeInterface
{
    private ErrorResponseGenerator $generator;

    public function setUp(): void
    {
        $this->generator = new ErrorResponseGenerator();
    }

    public function testGenerateByDefault(): void
    {
        $response = $this->generateResponse();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testGenerateWithNotSupportedAcceptHeader(): void
    {
        $response = $this->generateResponse('image/webp');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testGenerateWithNotSupportedErrorCode(): void
    {
        $response = $this->generateResponse('application/json,text/html;q=0.9,image/webp,*/*;q=0.8', 399);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());

        $response = $this->generateResponse(',application/xml,text/html;q=0.9,image/webp,*/*;q=0.8', 600);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(XmlResponse::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testGenerateWithSupportedAcceptHeaderAndErrorCode(): void
    {
        $response = $this->generateResponse('application/json', self::STATUS_BAD_REQUEST);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(self::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_BAD_REQUEST], $response->getReasonPhrase());
        $this->assertSame('{"name":"Error","code":400,"message":"Bad Request"}', (string) $response->getBody());

        $response = $this->generateResponse('text/plain', self::STATUS_NETWORK_AUTHENTICATION_REQUIRED);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame(self::STATUS_NETWORK_AUTHENTICATION_REQUIRED, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_NETWORK_AUTHENTICATION_REQUIRED], $response->getReasonPhrase());
        $this->assertSame('Error 511 - Network Authentication Required', (string) $response->getBody());
    }

    /**
     * @return array
     */
    public function supportedErrorCodeAllProvider(): array
    {
        return [
            // Client Errors 4xx
            self::STATUS_BAD_REQUEST => [self::STATUS_BAD_REQUEST],
            self::STATUS_UNAUTHORIZED => [self::STATUS_UNAUTHORIZED],
            self::STATUS_PAYMENT_REQUIRED => [self::STATUS_PAYMENT_REQUIRED],
            self::STATUS_FORBIDDEN => [self::STATUS_FORBIDDEN],
            self::STATUS_NOT_FOUND => [self::STATUS_NOT_FOUND],
            self::STATUS_METHOD_NOT_ALLOWED => [self::STATUS_METHOD_NOT_ALLOWED],
            self::STATUS_NOT_ACCEPTABLE => [self::STATUS_NOT_ACCEPTABLE],
            self::STATUS_PROXY_AUTHENTICATION_REQUIRED => [self::STATUS_PROXY_AUTHENTICATION_REQUIRED],
            self::STATUS_REQUEST_TIMEOUT => [self::STATUS_REQUEST_TIMEOUT],
            self::STATUS_CONFLICT => [self::STATUS_CONFLICT],
            self::STATUS_GONE => [self::STATUS_GONE],
            self::STATUS_LENGTH_REQUIRED => [self::STATUS_LENGTH_REQUIRED],
            self::STATUS_PRECONDITION_FAILED => [self::STATUS_PRECONDITION_FAILED],
            self::STATUS_PAYLOAD_TOO_LARGE => [self::STATUS_PAYLOAD_TOO_LARGE],
            self::STATUS_URI_TOO_LONG => [self::STATUS_URI_TOO_LONG],
            self::STATUS_UNSUPPORTED_MEDIA_TYPE => [self::STATUS_UNSUPPORTED_MEDIA_TYPE],
            self::STATUS_RANGE_NOT_SATISFIABLE => [self::STATUS_RANGE_NOT_SATISFIABLE],
            self::STATUS_EXPECTATION_FAILED => [self::STATUS_EXPECTATION_FAILED],
            self::STATUS_IM_A_TEAPOT => [self::STATUS_IM_A_TEAPOT],
            self::STATUS_MISDIRECTED_REQUEST => [self::STATUS_MISDIRECTED_REQUEST],
            self::STATUS_UNPROCESSABLE_ENTITY => [self::STATUS_UNPROCESSABLE_ENTITY],
            self::STATUS_LOCKED => [self::STATUS_LOCKED],
            self::STATUS_FAILED_DEPENDENCY => [self::STATUS_FAILED_DEPENDENCY],
            self::STATUS_TOO_EARLY => [self::STATUS_TOO_EARLY],
            self::STATUS_UPGRADE_REQUIRED => [self::STATUS_UPGRADE_REQUIRED],
            self::STATUS_PRECONDITION_REQUIRED => [self::STATUS_PRECONDITION_REQUIRED],
            self::STATUS_TOO_MANY_REQUESTS => [self::STATUS_TOO_MANY_REQUESTS],
            self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => [self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE],
            self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS => [self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS],
            // Server Errors 5xx
            self::STATUS_INTERNAL_SERVER_ERROR => [self::STATUS_INTERNAL_SERVER_ERROR],
            self::STATUS_NOT_IMPLEMENTED => [self::STATUS_NOT_IMPLEMENTED],
            self::STATUS_BAD_GATEWAY => [self::STATUS_BAD_GATEWAY],
            self::STATUS_SERVICE_UNAVAILABLE => [self::STATUS_SERVICE_UNAVAILABLE],
            self::STATUS_GATEWAY_TIMEOUT => [self::STATUS_GATEWAY_TIMEOUT],
            self::STATUS_VERSION_NOT_SUPPORTED => [self::STATUS_VERSION_NOT_SUPPORTED],
            self::STATUS_VARIANT_ALSO_NEGOTIATES => [self::STATUS_VARIANT_ALSO_NEGOTIATES],
            self::STATUS_INSUFFICIENT_STORAGE => [self::STATUS_INSUFFICIENT_STORAGE],
            self::STATUS_LOOP_DETECTED => [self::STATUS_LOOP_DETECTED],
            self::STATUS_NOT_EXTENDED => [self::STATUS_NOT_EXTENDED],
            self::STATUS_NETWORK_AUTHENTICATION_REQUIRED => [self::STATUS_NETWORK_AUTHENTICATION_REQUIRED],
        ];
    }

    /**
     * @dataProvider supportedErrorCodeAllProvider
     * @param int $statusCode
     */
    public function testGenerateWithSupportedAndErrorCodeAll(int $statusCode): void
    {
        $reasonPhrase = self::PHRASES[$statusCode];
        $response = $this->generateResponse('text/plain', $statusCode);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($reasonPhrase, $response->getReasonPhrase());
        $this->assertSame("Error {$statusCode} - {$reasonPhrase}", (string) $response->getBody());
    }

    /**
     * @return array
     */
    public function invalidAcceptHeaderQualityExceptTextPlainProvider(): array
    {
        return [
            ['application/json;q=10,*/*;q=9.0,text/plain;q=1.0,'],
            ['text/html;q=0.0000,text/html;q=0.9876,text/plain;q=0.000'],
            ['application/xml;q=1.0000,text/html;q=1.001,text/plain;Q=1.000'],
            ['application/json;q=string,text/html;q=9876,text/plain;q=0.999'],
        ];
    }

    /**
     * @dataProvider invalidAcceptHeaderQualityExceptTextPlainProvider
     * @param string $acceptHeaderValue
     */
    public function testGenerateWithInvalidAcceptHeaderQualityExceptTextPlain(string $acceptHeaderValue): void
    {
        $response = $this->generateResponse($acceptHeaderValue, self::STATUS_INTERNAL_SERVER_ERROR);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame(self::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(self::PHRASES[self::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());
    }

    public function testGenerateHtmlResponse(): void
    {
        $response = $this->generateResponse();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);

        $response = $this->generateResponse('text/html');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);

        $response = $this->generateResponse('*/*;q=0.9, application/json;q=0.89');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);

        $response = $this->generateResponse('text/html, text/plain, application/json');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);

        $response = $this->generateResponse('application/json;q=0.99, text/html, text/plain;q=0.98');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    public function testGenerateTextResponse(): void
    {
        $response = $this->generateResponse('text/plain');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(TextResponse::class, $response);

        $response = $this->generateResponse('text/plain, text/html, application/json');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(TextResponse::class, $response);

        $response = $this->generateResponse('application/json;q=0.99, text/plain, text/html;q=0.98');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(TextResponse::class, $response);
    }

    public function testGenerateJsonResponse(): void
    {
        $response = $this->generateResponse('application/json');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $response = $this->generateResponse('application/json, text/plain, text/html');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $response = $this->generateResponse('text/plain;q=0.99, application/json, text/html;q=0.98');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testGenerateXmlResponse(): void
    {
        $response = $this->generateResponse('text/xml');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(XmlResponse::class, $response);

        $response = $this->generateResponse('application/xml');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(XmlResponse::class, $response);

        $response = $this->generateResponse('application/xml, text/plain, text/html');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(XmlResponse::class, $response);

        $response = $this->generateResponse('text/plain;q=0.99, application/xml, text/html;q=0.98');
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(XmlResponse::class, $response);
    }

    /**
     * @param int $errorCode
     * @param string $acceptHeader
     * @return ResponseInterface
     */
    private function generateResponse(string $acceptHeader = '', int $errorCode = 0): ResponseInterface
    {
        return $this->generator->generate(
            $this->createThrowable($errorCode),
            $this->createServerRequest($acceptHeader)
        );
    }

    /**
     * @param int $errorCode
     * @return Throwable
     */
    private function createThrowable(int $errorCode = 0): Throwable
    {
        return new Exception('Test Error', $errorCode);
    }

    /**
     * @param string $acceptHeader
     * @return ServerRequestInterface
     */
    private function createServerRequest(string $acceptHeader = ''): ServerRequestInterface
    {
        $serverRequest = new ServerRequest();
        return empty($acceptHeader) ? $serverRequest : $serverRequest->withHeader('accept', $acceptHeader);
    }
}
