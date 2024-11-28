<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};

final class ItemType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Attribute',
            'fields' => [
                'displayValue' => Type::string(),
                'value' => Type::string(),
                'id' => Type::string()
            ]
        ];
        parent::__construct($config);
    }
}
