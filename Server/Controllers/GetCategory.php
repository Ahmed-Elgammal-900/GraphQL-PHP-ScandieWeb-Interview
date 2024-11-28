<?php

declare(strict_types=1);

namespace Server\Controllers;

use Server\Models\CategoryModel;

class GetCategory extends CategoryModel
{
    public function getType(): mixed
    {
        $sql = "SELECT * FROM category";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
}
