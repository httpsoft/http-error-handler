<?php

declare(strict_types=1);

namespace HttpSoft\ErrorHandler;

use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\TextResponse;
use HttpSoft\Response\XmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function array_key_exists;
use function array_keys;
use function explode;
use function preg_match;
use function strtolower;
use function trim;
use function uasort;

final class ErrorResponseGenerator implements ErrorResponseGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedArrayAccess
     */
    public function generate(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $errorCode = (int) $error->getCode();
        $responseCode = self::STATUS_INTERNAL_SERVER_ERROR;

        if (array_key_exists($errorCode, self::ERROR_PHRASES)) {
            $responseCode = $errorCode;
        }

        $requestMimeTypes = $this->getSortedMimeTypesByRequest($request);
        return $this->getResponse($responseCode, self::ERROR_PHRASES[$responseCode], $requestMimeTypes);
    }

    /**
     * @param int $code
     * @param string $message
     * @param string[] $mimeTypes
     * @return ResponseInterface
     */
    private function getResponse(int $code, string $message, array $mimeTypes): ResponseInterface
    {
        foreach ($mimeTypes as $mimeType) {
            if ($mimeType === 'text/html' || $mimeType === '*/*') {
                return $this->getHtmlResponse($code, $message);
            }

            if ($mimeType === 'text/plain') {
                return new TextResponse("Error {$code} - {$message}", $code);
            }

            if ($mimeType === 'application/json') {
                return new JsonResponse(['name' => 'Error', 'code' => $code, 'message' => $message], $code);
            }

            if ($mimeType === 'application/xml' || $mimeType === 'text/xml') {
                $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
                $xml .= "\n<error>\n<code>{$code}</code>\n<message>{$message}</message>\n</error>";
                return new XmlResponse($xml, $code);
            }
        }

        return $this->getHtmlResponse($code, $message);
    }

    /**
     * @param int $code
     * @param string $message
     * @return HtmlResponse
     */
    private function getHtmlResponse(int $code, string $message): HtmlResponse
    {
        $title = "Error {$code} - {$message}";
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>' . $title . '</title></head>';
        $html .= '<body style="padding:20px 10px"><h1 style="text-align:center">' . $title . '</h1></body></html>';
        return new HtmlResponse($html, $code);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string[]
     * @psalm-suppress MixedArrayOffset
     * @psalm-suppress MixedReturnTypeCoercion
     */
    private function getSortedMimeTypesByRequest(ServerRequestInterface $request): array
    {
        if (!$acceptParameters = $request->getHeaderLine('accept')) {
            return [];
        }

        $mimeTypes = [];

        foreach (explode(',', $acceptParameters) as $acceptParameter) {
            $parts = explode(';', $acceptParameter);

            if (!isset($parts[0]) || isset($mimeTypes[$parts[0]]) || !($mimeType = strtolower(trim($parts[0])))) {
                continue;
            }

            if (!isset($parts[1])) {
                $mimeTypes[$mimeType] = 1.0;
                continue;
            }

            if (preg_match('/^\s*q=\s*(0(?:\.\d{1,3})?|1(?:\.0{1,3})?)\s*$/i', $parts[1], $matches)) {
                $mimeTypes[$mimeType] = (float) ($matches[1] ?? 1.0);
            }
        }

        uasort($mimeTypes, static fn(float $a, float $b) => ($a === $b) ? 0 : ($a > $b ? -1 : 1));
        return array_keys($mimeTypes);
    }
}
