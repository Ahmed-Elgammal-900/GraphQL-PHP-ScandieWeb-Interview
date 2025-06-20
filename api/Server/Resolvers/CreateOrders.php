<?php

declare(strict_types=1);

namespace Api\Server\Resolvers;

use Api\Server\Models\OrdersModel;

class CreateOrders extends OrdersModel
{

    public function createOrders($items): string
    {
        for ($i = 0; $i < count($items['items']); $i++) {
            $type = $items['items'][$i]['type'];
            unset($items['items'][$i]['type']);

            $keys = array_keys($items['items'][$i]);
            $values = array_values($items['items'][$i]);
            $placeholders = array_fill(0, count($keys), '?');

            $type = preg_replace('/[^a-zA-Z0-9_]/', '', $type);
            $table = $type . "orders";

            $safeKeys = array_map(function ($key): string {
                return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            }, $keys);

            $keysString = implode(', ', $safeKeys);
            $placeholdersString = implode(', ', $placeholders);

            $sql = "INSERT INTO $table ($keysString) VALUES ($placeholdersString)";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
        }

        return 'Your Orders Have been received';
    }
}
