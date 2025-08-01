<?php

declare(strict_types=1);

namespace Api\Server\Resolvers;

use Api\Server\Models\ProductsModel;
use PDO;

class GetProduct extends ProductsModel
{
    public function getByID(): mixed
    {
        $sql = "SELECT id, `name`, instock, `description`, category, brand from products where id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":id", $this->productId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getProduct(string $category): mixed
    {
        $sql = "SELECT * FROM products where category = :category or :category_all = 'all' ORDER BY category";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":category", $category, PDO::PARAM_STR);
        $stmt->bindValue(":category_all", $category, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getGallery(): array
    {
        $sql = "SELECT gallery FROM gallery WHERE id = :productid";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":productid", $this->productId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $images = [];
        for ($i = 0; $i < count($result); $i++) {
            $images[] = $result[$i]['gallery'];
        }
        return $images;
    }

    public function getCurrency(): mixed
    {
        $sql = "SELECT label, sympol FROM products WHERE id = :productid";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":productid", $this->productId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPrice(): mixed
    {
        $sql = "SELECT id, amount FROM products WHERE id = :productid";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":productid", $this->productId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
}
