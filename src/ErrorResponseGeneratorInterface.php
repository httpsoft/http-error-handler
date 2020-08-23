<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use HttpSoft\Response\ResponseStatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorResponseGeneratorInterface extends ResponseStatusCodeInterface
{
    /**
     * Map of error HTTP status code and reason phrases.
     *
     * @link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     */
    public const ERROR_PHRASES = [
        // Client Errors 4xx
        self::STATUS_BAD_REQUEST => 'Bad Request',
        self::STATUS_UNAUTHORIZED => 'Unauthorized',
        self::STATUS_PAYMENT_REQUIRED => 'Payment Required',
        self::STATUS_FORBIDDEN => 'Forbidden',
        self::STATUS_NOT_FOUND => 'Not Found',
        self::STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::STATUS_NOT_ACCEPTABLE => 'Not Acceptable',
        self::STATUS_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::STATUS_REQUEST_TIMEOUT => 'Request Timeout',
        self::STATUS_CONFLICT => 'Conflict',
        self::STATUS_GONE => 'Gone',
        self::STATUS_LENGTH_REQUIRED => 'Length Required',
        self::STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        self::STATUS_PAYLOAD_TOO_LARGE => 'Payload Too Large',
        self::STATUS_URI_TOO_LONG => 'URI Too Long',
        self::STATUS_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::STATUS_RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
        self::STATUS_EXPECTATION_FAILED => 'Expectation Failed',
        self::STATUS_IM_A_TEAPOT => 'I\'m a teapot',
        self::STATUS_MISDIRECTED_REQUEST => 'Misdirected Request',
        self::STATUS_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        self::STATUS_LOCKED => 'Locked',
        self::STATUS_FAILED_DEPENDENCY => 'Failed Dependency',
        self::STATUS_TOO_EARLY => 'Too Early',
        self::STATUS_UPGRADE_REQUIRED => 'Upgrade Required',
        self::STATUS_PRECONDITION_REQUIRED => 'Precondition Required',
        self::STATUS_TOO_MANY_REQUESTS => 'Too Many Requests',
        self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        // Server Errors 5xx
        self::STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        self::STATUS_BAD_GATEWAY => 'Bad Gateway',
        self::STATUS_SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::STATUS_GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::STATUS_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        self::STATUS_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        self::STATUS_INSUFFICIENT_STORAGE => 'Insufficient Storage',
        self::STATUS_LOOP_DETECTED => 'Loop Detected',
        self::STATUS_NOT_EXTENDED => 'Not Extended',
        self::STATUS_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    /**
     * Generates an instance of `Psr\Http\Message\ResponseInterface` with information about the handled error.
     *
     * @param Throwable $error
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function generate(Throwable $error, ServerRequestInterface $request): ResponseInterface;
}
