<?php

declare(strict_types=1);

namespace Api\Utils;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Psr7\Response;
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

            ResponseInterface::class => fn(): ResponseInterface => new Response(),

            ItemType::class => fn(): ItemType => new ItemType(),

            CategoryType::class => fn(): CategoryType => new CategoryType(),

            CurrencyType::class => fn(): CurrencyType => new CurrencyType(),

            InputType::class => fn(): InputType => new InputType(),

            AttributesType::class => fn(ContainerInterface $container): AttributesType => new AttributesType($container->get(ItemType::class)),

            PriceType::class => fn(ContainerInterface $container): PriceType => new PriceType($container->get(CurrencyType::class)),

            ProductType::class => fn(ContainerInterface $container): ProductType => new ProductType($container->get(AttributesType::class), $container->get(PriceType::class)),

            QueryType::class => fn(ContainerInterface $container): QueryType => new QueryType($container->get(CategoryType::class), $container->get(ProductType::class)),

            MutationType::class => fn(ContainerInterface $container): MutationType => new MutationType($container->get(InputType::class)),
        ]);

        return $containerBuilder->build();
    }
}
