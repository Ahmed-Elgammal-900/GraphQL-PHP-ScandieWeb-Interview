<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Api\Utils\{GraphQLHandler, Container};
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Log\LoggerInterface;

$container = Container::createContainer();

$request = ServerRequestFactory::fromGlobals();

$handler = new GraphQLHandler(
    $container,
    $container->get(LoggerInterface::class)
);

$response = $handler->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
