<?php

declare(strict_types=1);

namespace Api\Utils;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Api\Types\{
    ItemType, 
    CategoryType, 
    CurrencyType, 
    AttributesType, 
    PriceType, 
    ProductType, 
    InputType, 
    QueryType, 
    MutationType
};

final class Container
{
    public static function createContainer(): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([

            ResponseInterface::class => function (): ResponseInterface {
                return (new ResponseFactory())->createResponse();
            },

            ItemType::class => function (): ItemType {
                return new ItemType();
            },

            CategoryType::class => function (): CategoryType {
                return new CategoryType();
            },

            CurrencyType::class => function (): CurrencyType {
                return new CurrencyType();
            },

            AttributesType::class => function (ContainerInterface $container): AttributesType {
                return new AttributesType($container->get(ItemType::class));
            },

            PriceType::class => function (ContainerInterface $container): PriceType {
                return new PriceType($container->get(CurrencyType::class));
            },

            ProductType::class => function (ContainerInterface $container): ProductType {
                return new ProductType($container->get(AttributesType::class), $container->get(PriceType::class));
            },

            InputType::class => function (): InputType {
                return new InputType();
            },

            QueryType::class => function (ContainerInterface $container): QueryType {
                return new QueryType($container->get(CategoryType::class), $container->get(ProductType::class));
            },

            MutationType::class => function (ContainerInterface $container): MutationType {
                return new MutationType($container->get(InputType::class));
            },
        ]);

        return $containerBuilder->build();
    }
}
