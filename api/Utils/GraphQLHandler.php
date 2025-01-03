<?php

declare(strict_types=1);

namespace Api\Utils;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Api\Types\{QueryType, MutationType};

class GraphQLHandler implements RequestHandlerInterface
{
    private Schema $schema;
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->initializeSchema();
    }

    private function initializeSchema(): void
    {
        $queryType = $this->container->get(QueryType::class);
        $mutationType = $this->container->get(MutationType::class);

        $this->schema = new Schema([
            'query' => $queryType,
            'mutation' => $mutationType,
        ]);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->createPreflightResponse();
        }

        try {
            $input = $this->parseInput($request);
            $result = $this->executeQuery($input['query'], $input['variables'] ?? null);

            return $this->createResponse(200, $result);
        } catch (\Throwable $e) {
            return $this->createResponse(500, [
                'errors' => [['message' => $e->getMessage()]],
            ]);
        }
    }

    private function createPreflightResponse(): ResponseInterface
    {
        $response = $this->container->get(ResponseInterface::class);

        $response = $response
            ->withStatus(200)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Content-Length', '0');

        return $response;
    }

    private function parseInput(ServerRequestInterface $request): array
    {
        $body = (string) $request->getBody();
        $input = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }

        if (!isset($input['query'])) {
            throw new \InvalidArgumentException('Query is required');
        }

        return $input;
    }

    private function executeQuery(string $query, ?array $variables = null): array
    {
        try {
            $result = GraphQL::executeQuery(
                $this->schema,
                $query,
                null,
                null,
                $variables
            );

            return $result->toArray();
        } catch (\Exception $e) {
            return [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                    ],
                ],
            ];
        }
    }

    private function createResponse(int $status, array $data): ResponseInterface
    {
        $response = $this->container->get(ResponseInterface::class);

        $response = $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true');

        $response->getBody()->write(json_encode($data));

        return $response;
    }
}
