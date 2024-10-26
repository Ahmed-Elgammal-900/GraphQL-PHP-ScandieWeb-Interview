<?php

require_once __DIR__ . '/vendor/autoload.php';

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\GraphQL;

include '../Server.php';

$categoryType = new ObjectType([
    'name' => 'Category',
    'fields' => [
        'name' => Type::string()
    ]
]);

$itemType = new ObjectType([
    'name' => 'Attribute',
    'fields' => [
        'displayValue' => Type::string(),
        'value' => Type::string(),
        'id' => Type::string()
    ]
]);

$attributesType = new ObjectType([
    'name' => 'AttributeSet',
    'fields' => [
        'id' => Type::string(),
        'items' => [
        'type' => Type::listof($itemType),
        'resolve' => function($attributes){
        $item = new getAtrributes($attributes['productid'], $attributes['id']);
        return $item->getAllItems();
    }
        ],
        'name' => Type::string(),
        'type' => Type::string(),
    ]
]);

$currencyType = new ObjectType([
    'name' => 'Currency',
    'fields' => [
        'label' => Type::string(),
        'sympol' => Type::string()
    ]
]);

$priceType = new ObjectType([
    'name' => 'Price',
    'fields' => [
        'amount' => Type::float(),
        'currency' => [
            'type' => $currencyType,
            'resolve' => function($price){
                $currency = new getProduct($price['id']);
                return $currency->getCurrency();
            }
        ]
    ]
]);


$productType = new ObjectType([
    'name' => 'Product',
    'fields' => [
        'id' => Type::string(),
        'name' => Type::string(),
        'instock' =>Type::string(),
        'gallery' => [
            'type' => Type::listof(Type::string()),
            'resolve' => function($product){
                $gallery = new getProduct($product['id']);
                return $gallery->getGallery();
            }
        ],
        'description' => Type::string(),
        'category' => Type::string(),
        'attributes' => [
            'type' => Type::listof($attributesType),
            'resolve' => function($product){
                $attr = new getAtrributes($product['id']);
                return $attr->getAttribute();
            }
        ],
        'prices' => [
            'type' => $priceType,
            'resolve' => function($product){
                $price = new getProduct($product['id']);
                return $price->getPrice();
            }
        ],
        'brand' => Type::string()
    ]
]);

$context = new contextData();

$queryType = new ObjectType([
    'name' => 'query',
    'fields' =>[
        'categories' =>[
            'type' => Type::listof($categoryType),
            'args' => [
                'name' => [
                    'type' => Type::string(),
                    'defaultValue' => null
                ]
            ],
            'resolve' => function($root, $args, $context){
                $context->type = $args['name'];
                $category = new getCategroy($args['name']);
                return $category->getType();
            }
        ],

        'product' =>[
            'type' => $productType,
            'args' =>[
                'id' => Type::nonNull(Type::string())
            ],
            'resolve' => function($root, $args){
                $type = new getProduct($args['id']);
                return $type->getByID();
            },
        ],

        'products' =>[
            'type' => Type::listof($productType),
            'resolve' => function($root, $args, $context){
                $all = new getProduct();
                return $all->getAll($context->type);
            }
        ]
    ]
]);

$inputType = new InputObjectType([
    'name' => 'ItemInput',
    'fields' => [
        'id' => Type::nonNull(Type::string()),
        'count' => Type::nonNull(Type::int()),
        'price' => Type::nonNull(Type::float()),
        'type' => Type::nonNull(Type::string()),
        'size' => Type::string(),
        'color' => Type::string(),
        'capacity' => Type::string(),
        'feature' => Type::string(),
        'featurename' => Type::string(),
    ]
]);

$mutationType = new ObjectType([
    'name' => 'mutation',
    'fields' =>[
        'createOrders' =>[
            'type' => Type::string(),
            'args' =>[
                'items' => Type::listof($inputType)
            ],
            'resolve' => function($root, $args){
                $Orders = new createOrders();
                return $Orders->create($args);
            }
        ]
    ]
]);

$schema = new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

try {
    $result = GraphQL::executeQuery($schema, $query, null, $context, $variableValues);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = [
        'errors' => [
            [
                'message' => $e->getMessage()
            ]
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($output);