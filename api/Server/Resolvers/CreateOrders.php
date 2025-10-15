<?php

declare(strict_types=1);

namespace Api\Server\Resolvers;

use Api\Server\Models\OrdersModel;
use DomainException;
use InvalidArgumentException;
use PDO;
use Exception;
use RuntimeException;

class CreateOrders extends OrdersModel
{
    private array $productCache = [];
    private int $batchSize = 20;

    protected function filterOrderKeys(array $orderItem): void
    {
        foreach ($orderItem as $key => $value) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                throw new InvalidArgumentException("Invalid field name: {$key}");
            }
        }
    }

    protected function validateOrderData(array $orderData): void
    {
        if (!isset($orderData['items']) || !is_array($orderData['items'])) {
            throw new InvalidArgumentException('Order data must contain an "items" array');
        }

        if (empty($orderData['items'])) {
            throw new InvalidArgumentException('No order items provided');
        }

        if (count($orderData['items']) > 100) {
            throw new RuntimeException("Items count exceeded");
        }
    }

    protected function getProductsData(array $orderData): void
    {
        $productIds = array_column($orderData['items'], 'id');
        if (count($orderData['items']) !== count($productIds)) {
            throw new RuntimeException("some orders don't have IDs");
        }
        $uniqueIds = array_unique($productIds);
        try {
            $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));

            $sql1 = "SELECT id, instock, amount as price, label, category FROM products WHERE id IN ({$placeholders})";

            $stmt1 = $this->connection->prepare($sql1);
            $stmt1->execute(array_values($uniqueIds));
            $products = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            if (count($products) !== count($uniqueIds)) {
                $foundIds = array_column($products, 'id');
                $notFound = array_diff($uniqueIds, $foundIds);
                throw new RuntimeException("Products not found: " . implode(', ', $notFound));
            }

            foreach ($products as $product) {
                $this->productCache[$product['id']] = [
                    'instock' => $product['instock'],
                    'price' => $product['price'],
                    'label' => $product['label'],
                    'category' => $product['category'],
                    'attributes' => []
                ];
            }

            $sql2 = "SELECT productid, type, GROUP_CONCAT(`value`) as attribute_values FROM productsattr 
            WHERE productid IN ({$placeholders}) GROUP BY productid, type";

            $stmt2 = $this->connection->prepare($sql2);
            $stmt2->execute(array_values($uniqueIds));
            $attributeRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            foreach ($attributeRows as $row) {
                $productId = $row['productid'];
                $values = explode(',', $row['attribute_values']);
                $this->productCache[$productId]['attributes'][$row['type']] = $values;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());

        }
    }

    protected function getProductData(string $productId): array
    {
        if (!isset($this->productCache[$productId])) {
            throw new RuntimeException("Product data not loaded: {$productId}");
        }

        return $this->productCache[$productId];
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
            $this->validateProductAttributes($orderData, $productData);
            $cleanData = $orderData;
        } else {
            $cleanData = $orderData;
            $cleanData['selectedOptions'] = null;
        }

        $cleanData['price'] *= $cleanData['count'];
        $cleanData['product_id'] = $cleanData['id'];
        unset($cleanData['id']);
        return $cleanData;
    }

    protected function validateProductAttributes(array $orderData, array $productData): void
    {
        if (!isset($orderData['selectedOptions'])) {
            throw new InvalidArgumentException("Selected options required for product: {$orderData['id']}");
        }

        $selectedOptions = $orderData['selectedOptions'];

        if (is_string($selectedOptions)) {
            $selectedOptions = json_decode($selectedOptions, true);
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
                throw new InvalidArgumentException(
                    "Invalid attribute type '{$attributeType}' for product: {$orderData['id']}"
                );
            }

            $validValues = $productData['attributes'][$attributeType];
            if (!in_array($selectedValue, $validValues, true)) {
                throw new DomainException(
                    "Invalid value '{$selectedValue}' for attribute '{$attributeType}'. " .
                    "Valid values: " . implode(', ', $validValues)
                );
            }
        }
    }

    protected function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function batchInsertNormalized(array $items): void
    {
        $columns = array_keys($items[0]);
        $chunks = array_chunk($items, $this->batchSize);

        foreach ($chunks as $chunk) {
            $columnNames = implode(', ', array_map([$this, 'escapeIdentifier'], $columns));

            $rowPlaceholder = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
            $allPlaceholders = implode(', ', array_fill(0, count($chunk), $rowPlaceholder));

            $sql = sprintf(
                "INSERT INTO orders (%s) VALUES %s",
                $columnNames,
                $allPlaceholders
            );


            $values = [];
            foreach ($chunk as $item) {
                $values = array_merge($values, array_values($item));
            }

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);
        }
    }

    public function processOrder(array $order): string
    {
        $this->validateOrderData($order);
        $orderID = 'ORD-' . strtoupper(bin2hex(random_bytes(16)));
        $this->getProductsData($order);

        $validatedItems = [];
        foreach ($order['items'] as $orderItem) {
            $orderItem['orderID'] = $orderID;
            $this->filterOrderKeys($orderItem);
            $validatedItems[] = $this->validateRequiredFields($orderItem);
        }

        $this->connection->beginTransaction();
        try {
            $this->batchInsertNormalized($validatedItems);
            $this->connection->commit();
        } catch (\Throwable $th) {
            $this->connection->rollBack();
            throw $th;

        }

        return 'Your Order has been received';
    }
}