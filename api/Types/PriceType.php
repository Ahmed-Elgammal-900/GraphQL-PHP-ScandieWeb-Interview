<?php

declare(strict_types=1);

namespace Api\Types;

use GraphQL\Type\Definition\{ObjectType, Type};
use Api\Server\Controllers\GetProduct;
use Api\Types\CurrencyType;

final class PriceType extends ObjectType
{
    public function __construct(CurrencyType $currencyType)
    {
        $config = [
            'name' => 'Price',
            'fields' => [
                'amount' => Type::float(),
                'currency' => [
                    'type' => $currencyType,
                    'resolve' => function ($price): mixed {
                        $currency = new GetProduct($price['id']);
                        return $currency->getCurrency();
                    }
                ]
            ]
        ];
        parent::__construct($config);
    }
}
