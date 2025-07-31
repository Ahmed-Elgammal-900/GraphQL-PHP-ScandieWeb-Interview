<?php

declare(strict_types=1);

namespace Api\Server\Resolvers;

use Api\Server\Models\OrdersModel;
use InvalidArgumentException;
use PDO;
use PDOException;

class CreateOrders extends OrdersModel
{

    protected function sanitizeOrderData(array $orderItem): void
    {
        foreach ($orderItem as $key => $value) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                throw new InvalidArgumentException("Invalid field name: {$key}");
            }
        }
    }

    protected function validateOrderType(string $type): void
    {
        try {
            $sql = "SELECT DISTINCT category from products";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        if (!is_string($type) || !in_array(strtolower(trim($type)), array_map('strtolower', $categories), true)) {
            throw new InvalidArgumentException("Invalid category");
        }
    }

    protected function validateOrderData(array $orderData)
    {
        if (!isset($orderData['items']) || !is_array($orderData['items'])) {
            throw new InvalidArgumentException('Order data must contain an "items" array');
        }

        if (empty($orderData['items'])) {
            throw new InvalidArgumentException('No order items provided');
        }

        if (count($orderData['items']) > 100) {
            throw new InvalidArgumentException('Too many orders in single request (max: 100)');
        }
    }

    protected function validateRequiredFields(array $orderData)
    {
        if (!is_string($orderData['id']) || !isset($orderData['id'])) {
            throw new InvalidArgumentException("Invalid Product id");
        }

        if (!is_int($orderData['count']) || !($orderData['count'] > 0)) {
            throw new InvalidArgumentException("Invalid count");
        }

        try {
            $sqlInstock = "SELECT instock from products where id = :id";
            $stmt2 = $this->connection->prepare($sqlInstock);
            $stmt2->bindValue(":id", $orderData['id'], PDO::PARAM_STR);
            $stmt2->execute();
            $instock = $stmt2->fetch(PDO::FETCH_COLUMN);

            $sqlAttr = "SELECT DISTINCT `type` from productsattr where productid = :id";
            $stmt = $this->connection->prepare($sqlAttr);
            $stmt->bindValue(":id", $orderData['id'], PDO::PARAM_STR);
            $stmt->execute();
            $attributesFields = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $sqlAttrValues = "SELECT `value` from productsattr where productid = :id";
            $stmt1 = $this->connection->prepare($sqlAttrValues);
            $stmt1->bindValue(":id", $orderData['id'], PDO::PARAM_STR);
            $stmt1->execute();
            $attributesValues = $stmt1->fetchAll(PDO::FETCH_COLUMN);

            $sqlPrice = "SELECT amount from products where id = :id";
            $stmt3 = $this->connection->prepare($sqlPrice);
            $stmt3->bindValue(":id", $orderData['id'], PDO::PARAM_STR);
            $stmt3->execute();
            $price = $stmt3->fetch(PDO::FETCH_COLUMN);

            $sqlCategory = "SELECT category from products where id = :id";
            $stmt4 = $this->connection->prepare($sqlCategory);
            $stmt4->bindValue(":id", $orderData['id'], PDO::PARAM_STR);
            $stmt4->execute();
            $category = $stmt4->fetch(PDO::FETCH_COLUMN);



        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        if ($instock !== "true") {
            throw new InvalidArgumentException("Product Not available");
        }

        if ($price !== $orderData['price']) {
            throw new InvalidArgumentException("Invalid Price");
        }

        $orderData['price'] *= $orderData['count'];

        if ($category !== $orderData['type']) {
            throw new InvalidArgumentException("Invalid Category");
        }

        if (!empty($attributesFields)) {
            if (!empty(array_diff(array_keys($orderData['selectedOptions']), $attributesFields)) || !empty(array_diff(array_values($orderData['selectedOptions']), $attributesValues))) {
                throw new InvalidArgumentException("Invalid Selections");
            }
        }

        $orderData = $this->flattenAssoc($orderData);

        return $orderData;
    }

    protected function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function flattenAssoc(array $orderItem)
    {
        $result = [];

        foreach ($orderItem as $key => $value) {
            if (is_array($value)) {
                $result += $this->flattenAssoc($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function processOrder($orderItem)
    {
        $this->sanitizeOrderData($orderItem);
        $this->validateOrderType($orderItem['type']);

        $data = $this->validateRequiredFields($orderItem);

        $tableName = $data['type'] . "orders";
        unset($data['type']);

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->escapeIdentifier($tableName),
            implode(', ', array_map([$this, 'escapeIdentifier'], $fields)),
            implode(', ', $placeholders)
        );

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_values($data));


    }

    public function processOrders(array $order): string
    {
        $this->validateOrderData($order);
        $orderID = 'ORD-' . strtoupper(bin2hex(random_bytes(16)));


        $this->connection->beginTransaction();

        try {
            foreach ($order['items'] as $orderItem) {
                $orderItem['orderID'] = $orderID;
                $this->processOrder($orderItem);
            }
            $this->connection->commit();
        } catch (\Throwable $th) {
            $this->connection->rollBack();
            throw $th;

        }

        return 'Your Orders Have been received';
    }
}