<?php

declare(strict_types=1);

namespace Api\Server\Resolvers;

use Api\Server\Models\CategoryModel;
use PDO;

class GetCategory extends CategoryModel
{
    public function getType(): mixed
    {
        $sql = "SELECT * FROM category";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
