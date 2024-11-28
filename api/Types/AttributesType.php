<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Server\Controllers\GetAttributes;

final class AttributesType extends ObjectType
{
    public function __construct(ItemType $itemType)
    {
        $config = [
            'name' => 'AttributeSet',
            'fields' => [
                'id' => Type::string(),
                'items' => [
                    'type' => Type::listof(type: $itemType),
                    'resolve' => function ($attributes): mixed {
                        $item = new GetAttributes($attributes['productid'], $attributes['id']);
                        return $item->getAllItems();
                    }
                ],
                'name' => Type::string(),
                'type' => Type::string(),
            ]
        ];

        parent::__construct($config);
    }
}
