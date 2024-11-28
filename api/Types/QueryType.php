<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Api\Server\Controllers\{GetCategory, GetProduct};
use Api\Types\CategoryType;

final class QueryType extends ObjectType
{
    public function __construct(CategoryType $categoryType, ProductType $productType)
    {
        $config = [
            'name' => 'Query',
            'fields' => [

                'categories' => [
                    'type' => Type::listof(new CategoryType()),
                    'resolve' => function ($root, $args): mixed {
                        $category = new GetCategory();
                        return $category->getType();
                    }
                ],

                'product' => [
                    'type' => $productType,
                    'args' => [
                        'id' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args): mixed {
                        $type = new GetProduct($args['id']);
                        return $type->getByID();
                    },
                ],

                'products' => [
                    'type' => Type::listof($productType),
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
