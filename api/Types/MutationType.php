<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Api\Server\Resolvers\CreateOrders;
use Api\Types\InputType;

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
                        'items' => Type::listOf($inputType)
                    ],
                    'resolve' => function ($root, $args): string {
                        $Orders = new CreateOrders();
                        return $Orders->processOrder($args);
                    }
                ]
            ]
        ];
        parent::__construct($config);
    }
}
