<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Server\Controllers\{GetAttributes, GetProduct};

final class ProductType extends ObjectType
{
    public function __construct(AttributesType $attributesType, PriceType $priceType)
    {
        $config = [
            'name' => 'Product',
            'fields' => [
                'id' => Type::string(),
                'name' => Type::string(),
                'instock' => Type::string(),
                'gallery' => [
                    'type' => Type::listof(Type::string()),
                    'resolve' => function ($product): array {
                        $gallery = new GetProduct($product['id']);
                        return $gallery->getGallery();
                    }
                ],
                'description' => Type::string(),
                'category' => Type::string(),
                'attributes' => [
                    'type' => Type::listof($attributesType),
                    'resolve' => function ($product): mixed {
                        $attr = new GetAttributes($product['id']);
                        return $attr->getAttribute();
                    }
                ],
                'prices' => [
                    'type' => $priceType,
                    'resolve' => function ($product): mixed {
                        $price = new GetProduct($product['id']);
                        return $price->getPrice();
                    }
                ],
                'brand' => Type::string()
            ]
        ];

        parent::__construct($config);

    }
}
