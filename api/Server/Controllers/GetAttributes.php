<?php

declare(strict_types=1);

namespace Api\Server\Controllers;

use Api\Server\Models\AttributesModel;
use PDO;

class GetAttributes extends AttributesModel
{
    public function getAttribute(): mixed
    {
        $sql = "SELECT productid, type as id, type as name, role as type FROM productsattr WHERE productid = :productid GROUP BY productid, type, role";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":productid", $this->productId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllItems(): mixed
    {
        $sql = "SELECT displayValue, value,  id FROM productsattr WHERE productid = :productid AND type = :type";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":productid", $this->productId, PDO::PARAM_STR);
        $stmt->bindValue(":type", $this->type, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
