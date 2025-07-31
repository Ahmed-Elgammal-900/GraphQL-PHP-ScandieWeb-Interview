<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{InputObjectType, Type};

final class InputType extends InputObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'ItemInput',
            'fields' => [
                'id' => Type::nonNull(Type::string()),
                'count' => Type::nonNull(Type::int()),
                'price' => Type::nonNull(Type::float()),
                'type' => Type::nonNull(Type::string()),
                'selectedOptions' => Type::listOf(Type::string()),
            ]
        ];
        parent::__construct($config);
    }
}
