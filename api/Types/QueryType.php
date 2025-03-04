<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Api\Server\Resolvers\{GetCategory, GetProduct};
use Api\Types\{CategoryType, ProductType};

final class QueryType extends ObjectType
{
    public function __construct(CategoryType $categoryType, ProductType $productType)
    {
        $config = [
            'name' => 'Query',
            'fields' => [
                'categories' => [
                    'type' => Type::listOf($categoryType),
                    'resolve' => function ($root, $args): mixed {
                        $category = new GetCategory();
                        return $category->getType();
                    }
                ],
                'products' => [
                    'type' => Type::listOf($productType),
                    'resolve' => function ($root, $args): mixed {
                        $all = new GetProduct();
                        return $all->getProduct();
                    }
                ],
            ]
        ];
        parent::__construct($config);
    }
}
