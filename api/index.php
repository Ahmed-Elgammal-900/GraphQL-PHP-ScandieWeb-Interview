<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Api\Utils\{GraphQLHandle, Container};

$container = Container::createContainer();

// $request = ServerRequestFactory::fromGlobals();

// $handler = new GraphQLHandler($container);

// $response = $handler->handle($request);

// http_response_code($response->getStatusCode());
// foreach ($response->getHeaders() as $name => $values) {
//     foreach ($values as $value) {
//         header(sprintf('%s: %s', $name, $value), false);
//     }
// }

// echo $response->getBody();

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

$graphQL = new GraphQLHandle($container);
$output = $graphQL->executeQuery($query, $variableValues);

header('Content-Type: application/json');
echo json_encode($output);