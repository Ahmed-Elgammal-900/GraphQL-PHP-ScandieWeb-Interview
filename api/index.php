<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Api\Utils\{GraphQLHandler, Container};
use GuzzleHttp\Psr7\ServerRequest;
use FastRoute\{RouteCollector};

$request = ServerRequest::fromGlobals();
$httpMethod = $request->getMethod();
$uri = $request->getUri()->getPath();


$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r): void {
    $r->post('/graphql', [GraphQLHandler::class, 'handle']);
    $r->addRoute('OPTIONS', '/graphql', [GraphQLHandler::class, 'handle']);
});

$routeInfo = $dispatcher->dispatch(
    $httpMethod,
    $uri
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Method Not Allowed', 'allowed' => $allowedMethods]);
        break;
    case FastRoute\Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];

        $container = Container::createContainer();
        $graphQL = new $class($container);
        $response = $graphQL->$method($request);

        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        echo $response->getBody();
        break;
}