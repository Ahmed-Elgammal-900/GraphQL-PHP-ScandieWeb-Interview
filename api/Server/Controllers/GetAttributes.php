<?php

declare(strict_types=1);

namespace Api\Server\Controllers;

use Api\Server\Models\AttributesModel;

class GetAttributes extends AttributesModel
{
    public function getAttribute(): mixed
    {
        $sql = "SELECT productid, type as id, type as name, role as type FROM productsattr WHERE productid = ? GROUP BY productid, type, role";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function getAllItems(): mixed
    {
        $sql = "SELECT displayValue, value,  id FROM productsattr WHERE productid = ? AND type = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ss", $this->id, $this->type);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
}
