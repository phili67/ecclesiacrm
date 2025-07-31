<?php

namespace EcclesiaCRM\Slim\Error;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use EcclesiaCRM\dto\SystemConfig;
use Throwable;

// use : throw new HttpBadRequestException($request, _('Bad request')); in v2 route for example error 400
// use : throw new HttpNotFoundException($request, _('Document not found')); in v2 route for example error 404
// use : throw new HttpMethodNotAllowedException($request, _(Method 'Not allowed')); in v2 route for example error 405
// use : throw new HttpUnauthorizedException($request, _('Forbidden')); in v2 route for example error 401
// use : throw new HttpInternalServerErrorException($request, _('Internal server error')); in v2 route for example error 500
// use : throw new HttpForbiddenException($request, _('Forbidenne exception')); in v2 route for example error 403

class handlers {
    protected $App;

    public function __construct($App = null) {
        $this->App = $App;
    }

    // errorHandler
    public function customErrorHandler (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $response = $this->App->getResponseFactory()->createResponse();

        $data = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => explode("\n", $exception->getTraceAsString())
        ];

        return $response->getBody()
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));    
    }

    // notFoundHandler
    public function customNotFoundErrorHandler (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    )  {
        $response = new Response();
        if ( strstr( $request->getUri(), '/api/' ) ) {
            $response = new Response();
            $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
            return $response->withStatus(404);
        } else {
            return $response
                ->withHeader('Location', '/v2/error/404/' . $request->getMethod() . '/' . str_replace('/', ' ', $request->getUri()))
                ->withStatus(302);
        }
    }

    // notAllowedHandler
    public function customNotAllowedErrorHandler (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $response = new Response();

        return $response->getBody()->write('Method must be one of: ' .  $exception->getMessage());
    }

    // notFoundHandler
    public function customUnauthorizedErrorHandler (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    )  {
        $response = new Response();
        $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
        return $response->withStatus(401);    
    }

    // notFoundHandler
    public function customInternalServerErrorHandler (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $response = new Response();
        $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
        return $response->withStatus(500);    
    }

    // notFoundHandler
    public function customForbiddenErrorHandler (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $response = new Response();
        $response->getBody()->write($exception->getMessage() . " : " . $request->getMethod() . ' on ' . $request->getUri());
        return $response->withStatus(403);    
    }

    public function installHandlers () {
        // Add Error Middleware
        if (SystemConfig::getValue('sLogLevel') == 0) {
            $errorMiddleware = $this->App->addErrorMiddleware(false, false, false);
        } else {
            $errorMiddleware = $this->App->addErrorMiddleware(true, true, true);
        }

        $errorMiddleware->setErrorHandler(\Slim\Exception\HttpBadRequestException::class, handlers::class.':customErrorHandler');
        $errorMiddleware->setErrorHandler(\Slim\Exception\HttpNotFoundException::class, handlers::class.':customNotFoundErrorHandler');
        $errorMiddleware->setErrorHandler(\Slim\Exception\HttpMethodNotAllowedException::class, handlers::class.':customNotAllowedErrorHandler');
        $errorMiddleware->setErrorHandler(\Slim\Exception\HttpUnauthorizedException::class, handlers::class.':customUnauthorizedErrorHandler');
        $errorMiddleware->setErrorHandler(\Slim\Exception\HttpInternalServerErrorException::class, handlers::class.':customInternalServerErrorHandler');
        $errorMiddleware->setErrorHandler(\Slim\Exception\HttpForbiddenException::class, handlers::class.':customForbiddenErrorHandler');
    }
}

