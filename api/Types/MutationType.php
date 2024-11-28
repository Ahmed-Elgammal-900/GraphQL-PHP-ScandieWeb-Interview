<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Server\Controllers\CreateOrders;

final class MutationType extends ObjectType
{
    public function __construct(InputType $inputType)
    {
        $config = [
            'name' => 'Mutation',
            'fields' => [
                'createOrders' => [
                    'type' => Type::string(),
                    'args' => [
                        'items' => Type::listof($inputType)
                    ],
                    'resolve' => function ($root, $args): string {
                        $Orders = new CreateOrders();
                        return $Orders->create($args);
                    }
                ]
            ]
        ];

        parent::__construct($config);
    }
}
