<?php

declare(strict_types=1);

namespace Server\Controllers;

use Server\Models\ProductsModel;

class GetProduct extends ProductsModel
{
    public function getProduct(): mixed
    {
        $sql = "SELECT * FROM products ORDER BY category";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function getByID(): mixed
    {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $this->productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function getGallery(): array
    {
        $sql = "SELECT gallery FROM gallery WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $this->productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $images = [];
        for ($i = 0; $i < count($data); $i++) {
            $images[] = $data[$i]['gallery'];
        }
        return $images;
    }

    public function getCurrency(): mixed
    {
        $sql = "SELECT label, sympol FROM products WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $this->productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function getPrice(): mixed
    {
        $sql = "SELECT id, amount FROM products WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $this->productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }
}
