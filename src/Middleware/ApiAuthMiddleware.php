<?php

namespace App\Middleware;

use App\DataService\ApiTokenQueryService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class ApiAuthMiddleware
{
    private ApiTokenQueryService $apiTokenQueryService;
    public function __construct(ContainerInterface $container) {
        $this->apiTokenQueryService = $container->get('apiTokenQueryService');
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $token = $request->getQueryParams()['token'];

        if ($token == null || !$this->apiTokenQueryService->TokenIsValid($token)) {
            $response = new Response();
            $response = $response->withStatus(401);
            $response->getBody()->write("401 Unauthorized");
            return $response;
        }

        return $handler->handle($request);
    }
}