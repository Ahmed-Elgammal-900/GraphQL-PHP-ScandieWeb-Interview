<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Api\Utils\{GraphQLHandler, Container};
use GuzzleHttp\Psr7\ServerRequest;

$container = Container::createContainer();

$request = ServerRequest::fromGlobals();

$handler = new GraphQLHandler($container);

$response = $handler->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();