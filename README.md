# HTTP Error Handler

[![License](https://poser.pugx.org/httpsoft/http-error-handler/license)](https://packagist.org/packages/httpsoft/http-error-handler)
[![Latest Stable Version](https://poser.pugx.org/httpsoft/http-error-handler/v)](https://packagist.org/packages/httpsoft/http-error-handler)
[![Total Downloads](https://poser.pugx.org/httpsoft/http-error-handler/downloads)](https://packagist.org/packages/httpsoft/http-error-handler)
[![GitHub Build Status](https://github.com/httpsoft/http-error-handler/workflows/build/badge.svg)](https://github.com/httpsoft/http-error-handler/actions)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/httpsoft/http-error-handler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-error-handler/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/httpsoft/http-error-handler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-error-handler/?branch=master)

This package implements [Psr\Http\Server\MiddlewareInterface](https://github.com/php-fig/http-server-middleware/blob/master/src/MiddlewareInterface.php) and [Psr\Http\Server\RequestHandlerInterface](https://github.com/php-fig/http-server-handler/blob/master/src/RequestHandlerInterface.php).

## Documentation

* [In English language](https://httpsoft.org/docs/error-handler).
* [In Russian language](https://httpsoft.org/ru/docs/error-handler).

## Installation

This package requires PHP version 7.4 or later.

```
composer require httpsoft/http-error-handler
```

## Usage ErrorHandler

```php
use HttpSoft\ErrorHandler\ErrorHandler;

/**
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var Psr\Http\Server\RequestHandlerInterface $handler
 *
 * @var HttpSoft\ErrorHandler\ErrorListenerInterface $logErrorListener
 * @var HttpSoft\ErrorHandler\ErrorListenerInterface $sendErrorListener
 * @var HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface $responseGenerator
 */

$errorHandler = new ErrorHandler($handler, $responseGenerator);

$errorHandler->addListener($logErrorListener);
$errorHandler->addListener($sendErrorListener);

/** @var \Psr\Http\Message\ResponseInterface $response */
$response = $errorHandler->handle($request);
```

## Usage ErrorHandlerMiddleware

```php
use HttpSoft\ErrorHandler\ErrorHandlerMiddleware;

/**
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var Psr\Http\Server\RequestHandlerInterface $handler
 *
 * @var HttpSoft\ErrorHandler\ErrorListenerInterface $logErrorListener
 * @var HttpSoft\ErrorHandler\ErrorListenerInterface $sendErrorListener
 * @var HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface $responseGenerator
 */

$errorHandler = new ErrorHandlerMiddleware($responseGenerator);

$errorHandler->addListener($logErrorListener);
$errorHandler->addListener($sendErrorListener);

/** @var \Psr\Http\Message\ResponseInterface $response */
$response = $errorHandler->process($request, $handler);
```
