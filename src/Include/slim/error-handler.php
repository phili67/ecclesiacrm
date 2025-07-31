<?php

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use EcclesiaCRM\dto\SystemConfig;

// use : throw new HttpBadRequestException($request, _('Bad request')); in v2 route for example error 400
// use : throw new HttpNotFoundException($request, _('Document not found')); in v2 route for example error 404
// use : throw new HttpMethodNotAllowedException($request, _(Method 'Not allowed')); in v2 route for example error 405
// use : throw new HttpUnauthorizedException($request, _('Forbidden')); in v2 route for example error 401
// use : throw new HttpInternalServerErrorException($request, _('Internal server error')); in v2 route for example error 500
// use : throw new HttpForbiddenException($request, _('Forbidenne exception')); in v2 route for example error 403


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

// notFoundHandler
$customUnauthorizedErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = new Response();
    $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
    return $response->withStatus(401);    
};

// notFoundHandler
$customInternalServerErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = new Response();
    $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
    return $response->withStatus(500);    
};

// notFoundHandler
$customForbiddenErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = new Response();
    $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
    return $response->withStatus(403);    
};

// Add Error Middleware
if (SystemConfig::getValue('sLogLevel') == 0) {
    $errorMiddleware = $app->addErrorMiddleware(false, false, false);
} else {
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);
}

$errorMiddleware->setErrorHandler(Slim\Exception\HttpBadRequestException::class, $customErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpNotFoundException::class, $customNotFoundErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpMethodNotAllowedException::class, $customNotAllowedErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpUnauthorizedException::class, $customUnauthorizedErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpInternalServerErrorException::class, $customInternalServerErrorHandler);
$errorMiddleware->setErrorHandler(Slim\Exception\HttpForbiddenException::class, $customForbiddenErrorHandler);

