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
                    'args' => [
                        'category' => Type::string()
                    ],
                    'resolve' => function ($root, $args): mixed {
                        $all = new GetProduct();
                        return $all->getByCategory($args['category']);
                    }
                ],

                'product' => [
                    'type' => $productType,
                    'args' => [
                        'id' => Type::string()
                    ],
                    'resolve' => function ($root, $args) {
                        $product = new GetProduct($args['id']);
                        return $product->getById();
                    }
                ]

            ]
        ];
        parent::__construct($config);
    }
}
