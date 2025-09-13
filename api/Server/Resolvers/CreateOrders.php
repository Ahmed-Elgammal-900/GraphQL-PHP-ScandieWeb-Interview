<?php

declare(strict_types=1);

namespace Api\Server\Resolvers;

use Api\Server\Models\OrdersModel;
use DomainException;
use InvalidArgumentException;
use PDO;
use PDOException;
use Exception;

class CreateOrders extends OrdersModel
{
    private array $productCache = [];

    protected function sanitizeOrderData(array $orderItem): void
    {
        foreach ($orderItem as $key => $value) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                throw new InvalidArgumentException("Invalid field name: {$key}");
            }
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
    }

    protected function getProductData($productId)
    {
        if (isset($this->productCache[$productId])) {
            return $this->productCache[$productId];
        }

        try {
            $sql1 = "SELECT instock, amount as price, label, category FROM products WHERE id = :id";

            $stmt1 = $this->connection->prepare($sql1);
            $stmt1->bindValue(":id", $productId, PDO::PARAM_STR);
            $stmt1->execute();
            $basicData = $stmt1->fetch(PDO::FETCH_ASSOC);

            if (!$basicData) {
                throw new InvalidArgumentException("Product not found");
            }


            $sql2 = "SELECT type, GROUP_CONCAT(`value`) as attribute_values FROM productsattr WHERE productid = :id GROUP BY `type`";

            $stmt2 = $this->connection->prepare($sql2);
            $stmt2->bindValue(":id", $productId, PDO::PARAM_STR);
            $stmt2->execute();
            $attributeRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());

        }

        $attributes = [];

        foreach ($attributeRows as $row) {
            $values = explode(',', $row['attribute_values']);
            $attributes[$row['type']] = $values;
        }

        $productData = [
            'instock' => $basicData['instock'],
            'price' => (float) $basicData['price'],
            'label' => $basicData['label'],
            'category' => $basicData['category'],
            'attributes' => $attributes
        ];

        $this->productCache[$productId] = $productData;
        return $productData;

    }

    protected function validateRequiredFields(array $orderData): array
    {

        if (!isset($orderData['id']) || !is_string($orderData['id']) || empty(trim($orderData['id']))) {
            throw new InvalidArgumentException("Invalid Product id");
        }


        if (!isset($orderData['count']) || !is_int($orderData['count']) || $orderData['count'] <= 0) {
            throw new InvalidArgumentException("Invalid count");
        }


        if (!isset($orderData['price']) || !is_float($orderData['price']) || $orderData['price'] <= 0) {
            throw new InvalidArgumentException("Invalid price");
        }


        if (!isset($orderData['category']) || !is_string($orderData['category']) || empty(trim($orderData['category']))) {
            throw new InvalidArgumentException("Invalid product category");
        }

        $productData = $this->getProductData($orderData['id']);


        if ($productData['instock'] !== "true") {
            throw new DomainException("Product not available: {$orderData['id']}");
        }

        if ($orderData['price'] !== $productData['price'] || $orderData['label'] !== $productData['label']) {
            throw new DomainException(
                "Incorrect price"
            );
        }

        if ($productData['category'] !== $orderData['category']) {
            throw new DomainException(
                "Invalid category. Expected: {$productData['category']}, provided: {$orderData['category']}"
            );
        }

        if (!empty($productData['attributes'])) {
            if (!isset($orderData['selectedOptions'])) {
                throw new InvalidArgumentException("Selected options required for product: {$orderData['id']}");
            }

            $selectedOptions = $orderData['selectedOptions'];

            if (is_string($selectedOptions)) {
                $selectedOptions = json_decode($orderData['selectedOptions'], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException("Invalid JSON in selectedOptions: " . json_last_error_msg());
                }
            }


            if (!is_array($selectedOptions)) {
                throw new InvalidArgumentException("selectedOptions must be an array");
            }

            foreach ($productData['attributes'] as $requiredType => $validValues) {
                if (!isset($selectedOptions[$requiredType])) {
                    throw new InvalidArgumentException("Missing required attribute: {$requiredType}");
                }
            }

            foreach ($selectedOptions as $attributeType => $selectedValue) {
                if (!array_key_exists($attributeType, $productData['attributes'])) {
                    throw new InvalidArgumentException("Invalid attribute type '{$attributeType}' for product: {$orderData['id']}");
                }

                $validValues = $productData['attributes'][$attributeType];
                if (!in_array($selectedValue, $validValues, true)) {
                    throw new DomainException(
                        "Invalid value '{$selectedValue}' for attribute '{$attributeType}'."
                    );
                }
            }

            $cleanData = $orderData;
            $cleanData['selectedOptions'] = $selectedOptions;
        } else {
            $cleanData = $orderData;
            unset($cleanData['selectedOptions']);
        }

        $cleanData['price'] *= $cleanData['count'];

        return $this->flattenAssoc($cleanData);
    }

    protected function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function flattenAssoc(array $orderItem): array
    {
        $result = [];

        foreach ($orderItem as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $optionKey => $optionValue) {
                    $result[$optionKey] = $optionValue;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    protected function processOrder($orderItem)
    {
        $this->sanitizeOrderData($orderItem);

        $data = $this->validateRequiredFields($orderItem);
        $tableName = $data['category'] . "orders";
        unset($data['category']);

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