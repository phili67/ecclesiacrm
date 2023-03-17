<?php

namespace EcclesiaCRM\Slim\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use EcclesiaCRM\Http\Factory\ResponseFactory;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use EcclesiaCRM\Slim\Middleware\JWTMiddleware\RequestMethodRule;
use EcclesiaCRM\Slim\Middleware\JWTMiddleware\RequestPathRule;

class JWTMiddleware implements MiddlewareInterface {
    /**
     * PSR-3 compliant logger.
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * Last error message.
     * @var string
     */
    private $message;

    /**
     * The rules stack.
     * @var SplStack<RuleInterface>
     */
    private $rules;

    /**
     * Stores all the options passed to the middleware.
     *
     * @var array{
     *   secret?: string|array<string>,
     *   secure: bool,
     *   relaxed: array<string>,
     *   algorithm: string,
     *   regexp: string,
     *   cookie: string,
     *   attribute: string,
     *   path: array<string>,
     *   ignore: array<string>,
     *   before: null|callable,
     *   after: null|callable,
     *   error: null|callable,
     * }
     */
    private $options = [
        "secure" => true,
        "path" => ["/"],
        "algorithm" => "HS256",
        "regexp" => "/Bearer\s+(.*)$/i",
        "cookie" => "token",
        "ignore" => [],
        "before" => null,
        "after" => null,
        "error" => null
    ];

    /**
     * @param array{
     *   secret?: string|array<string>,
     *   secure?: bool,
     *   relaxed?: array<string>,
     *   algorithm?: array<string>,
     *   regexp?: string,
     *   cookie?: string,
     *   attribute?: string,
     *   path?: array<string>,
     *   ignore?: array<string>,
     *   before?: null|callable,
     *   after?: null|callable,
     *   error?: null|callable,
     * } $options
     */
    public function __construct(array $options = [])
    {
        /* Setup stack for rules */
        $this->rules = new \SplStack;

        /* Store passed in options overwriting any defaults. */
        $this->hydrate($options);

        /* If nothing was passed in options add default rules. */
        /* This also means $options["rules"] overrides $options["path"] */
        /* and $options["ignore"] */
        if (!isset($options["rules"])) {
            $this->rules->push(new RequestMethodRule([
                "ignore" => ["OPTIONS"]
            ]));
            $this->rules->push(new RequestPathRule([
                "path" => $this->options["path"],
                "ignore" => $this->options["ignore"]
            ]));
        }
    }

    /**
     * Hydrate options from given array.
     *
     * @param mixed[] $data
     */
    private function hydrate(array $data = []): void
    {
        foreach ($data as $key => $value) {
            /* https://github.com/facebook/hhvm/issues/6368 */
            $key = str_replace(".", " ", $key);
            $method = lcfirst(ucwords($key));
            $method = str_replace(" ", "", $method);
            if (method_exists($this, $method)) {
                /* Try to use setter */
                /** @phpstan-ignore-next-line */
                call_user_func([$this, $method], $value);
            } else {
                /* Or fallback to setting option directly */
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Process a request in PSR-15 style and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();

        /* If rules say we should not authenticate call next and return. */
        if (false === $this->shouldAuthenticate($request)) {
            return $handler->handle($request);
        }

        /* HTTP allowed only if secure is false or server is in relaxed array. */
        if ("https" !== $scheme && true === $this->options["secure"]) {
            if (!in_array($host, $this->options["relaxed"])) {
                $message = sprintf(
                    "Insecure use of middleware over %s denied by configuration.",
                    strtoupper($scheme)
                );
                throw new \RuntimeException($message);
            }
        }

        /* If token cannot be found or decoded return with 401 Unauthorized. */
        try {
            $token = $this->fetchToken($request);
            $decoded = $this->decodeToken($token);
        } catch (\RuntimeException | \DomainException $exception) {
            $response = (new ResponseFactory)->createResponse(401);
            return $this->processError($response, [
                "message" => $exception->getMessage(),
                "uri" => (string)$request->getUri()
            ]);
        }

        $params = [
            "decoded" => $decoded,
            "token" => $token,
        ];

        /* Add decoded token to request as attribute when requested. */
        if (array_key_exists('attribute', $this->options)) {
            $request = $request->withAttribute($this->options["attribute"], $decoded);
        }

        /* Modify $request before calling next middleware. */
        if (is_callable($this->options["before"])) {
            $beforeRequest = $this->options["before"]($request, $params);
            if ($beforeRequest instanceof ServerRequestInterface) {
                $request = $beforeRequest;
            }
        }

        /* Everything ok, call next middleware. */
        $response = $handler->handle($request);

        /* Modify $response before returning. */
        if (is_callable($this->options["after"])) {
            $afterResponse = $this->options["after"]($response, $params);
            if ($afterResponse instanceof ResponseInterface) {
                return $afterResponse;
            }
        }

        return $response;
    }

    /**
     * Fetch the access token.
     */
    private function fetchToken(ServerRequestInterface $request): string
    {
        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();

        if (isset($cookieParams[$this->options["cookie"]])) {
            $this->log(LogLevel::DEBUG, "Using token from cookie");
            if (preg_match($this->options["regexp"], $cookieParams[$this->options["cookie"]], $matches)) {
                return $matches[1];
            }
            return $cookieParams[$this->options["cookie"]];
        };

        /* If everything fails log and throw. */
        $this->log(LogLevel::WARNING, "Token not found");
        throw new \RuntimeException("Token not found.");
    }

    /**
     * Decode the token.
     *
     * @return mixed[]
     */
    private function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(
                    $this->options["secret"],
                    $this->options["algorithm"]
                )
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            $this->log(LogLevel::WARNING, $exception->getMessage(), [$token]);
            throw $exception;
        }
    }

    /**
     * Call the error handler if it exists.
     *
     * @param mixed[] $arguments
     */
    private function processError(ResponseInterface $response, array $arguments): ResponseInterface
    {
        if (is_callable($this->options["error"])) {
            $handlerResponse = $this->options["error"]($response, $arguments);
            if ($handlerResponse instanceof ResponseInterface) {
                return $handlerResponse;
            }
        }
        return $response;
    }

    /**
     * Check if middleware should authenticate.
     */
    private function shouldAuthenticate(ServerRequestInterface $request): bool
    {
        /* If any of the rules in stack return false will not authenticate */
        foreach ($this->rules as $callable) {
            if (false === $callable($request)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Set the logger.
     */
    private function logger(LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed[] $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
