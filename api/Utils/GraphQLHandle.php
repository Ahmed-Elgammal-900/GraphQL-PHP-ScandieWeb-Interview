<?php

declare(strict_types=1);

namespace Api\Utils;

use Psr\Container\ContainerInterface;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Api\Types\{QueryType, MutationType};


class GraphQLHandle
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

    public function executeQuery(string $query, ?array $variables = null): array
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
            error_log("Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                    ]
                ]
            ];
        }
    }
}