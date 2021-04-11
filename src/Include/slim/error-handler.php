<?php

use Psr\Http\Message\ServerRequestInterface as Request;

// errorHandler
$customErrorHandler = function (
    Request $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    $data = [
        'code' => $exception->getCode(),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => explode("\n", $exception->getTraceAsString())
    ];

    return $response->withStatus(500)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode($data));
};

// notFoundHandler
$customNotFoundErrorHandler = function (
    Request $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write("Can't find route for " . $request->getMethod() . ' on ' . $request->getUri() );
    return $response->withStatus(404);
};

// notAllowedHandler
$customNotAllowedErrorHandler = function (
    Request $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    return $response->withStatus(405)
        ->withHeader('Allow', $exception->getMessage())
        ->withHeader('Content-type', 'text/html')
        ->write('Method must be one of: ' . implode(', ', $exception->getMessage()));
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(Slim\Exception\HttpBadRequestException::class, $customErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpNotFoundException::class, $customNotFoundErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpNotAllowedException::class, $customNotAllowedErrorHandler);
