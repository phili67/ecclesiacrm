<?php

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// use : throw new HttpNotFoundException($request, _('Document not found')); in v2 route for example

// errorHandler
$customErrorHandler = function (
    Request $request,
    Throwable $exception,
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

    return $response->getBody()
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));    
};

// notFoundHandler
$customNotFoundErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    if ( strstr( $request->getUri(), '/api/' ) ) {
        $response = new Response();
        $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
        return $response->withStatus(404);
    } else {
        return $response->withRedirect('/v2/error/404/' . $request->getMethod() . '/' . str_replace('/', ' ', $request->getUri()));
    }
};

// notAllowedHandler
$customNotAllowedErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    return $response->getBody()->write('Method must be one of: ' .  $exception->getMessage());
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(Slim\Exception\HttpBadRequestException::class, $customErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpNotFoundException::class, $customNotFoundErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpMethodNotAllowedException::class, $customNotAllowedErrorHandler);
