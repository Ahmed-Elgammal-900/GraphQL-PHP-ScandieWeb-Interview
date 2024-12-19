<?php

declare(strict_types=1);

namespace Api\Server\Controllers;

use Api\Server\Models\ProductsModel;
use PDO;

class GetProduct extends ProductsModel
{
    public function getProduct(): mixed
    {
        $sql = "SELECT * FROM products ORDER BY category";
        $stmt = $this->connection->prepare($sql);
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
