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
    private array $productsCache = [];
    private int $batchSize = 20;

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

    protected function checkColumnsCount(array $items): void
    {
        $firstItem = $items[0];

        foreach ($items as $item) {

            $missing = array_diff_key($firstItem, $item);
            $extra = array_diff_key($item, $firstItem);

            if ($missing || $extra) {
                throw new InvalidArgumentException(
                    "Item has mismatched keys. " .
                    ($missing ? "Missing: " . implode(', ', array_keys($missing)) . ". " : "") .
                    ($extra ? "Extra: " . implode(', ', array_keys($extra)) : "")
                );
            }
        }
    }

    protected function getProductsData(array $orderData): void
    {
        $productIds = array_column($orderData['items'], 'id');

        if (empty($productIds)) {
            throw new RuntimeException("No product IDs founds in order data");
        }

        foreach ($productIds as $id) {
            if (!isset($id) || !is_string($id) || empty(trim($id))) {
                throw new InvalidArgumentException("Invalid Product id");
            }
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
                $this->productsCache[$product['id']] = [
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
                $this->productsCache[$productId]['attributes'][$row['type']] = $values;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());

        }
    }

    protected function validateRequiredFields(array $orderItem): array
    {
        if (!isset($orderItem['count']) || !is_int($orderItem['count']) || $orderItem['count'] <= 0) {
            throw new InvalidArgumentException("Invalid count");
        }


        if (!isset($orderItem['price']) || !is_float($orderItem['price']) || $orderItem['price'] <= 0) {
            throw new InvalidArgumentException("Invalid price");
        }


        if (!isset($orderItem['category']) || !is_string($orderItem['category']) || empty(trim($orderItem['category']))) {
            throw new InvalidArgumentException("Invalid product category");
        }

        $productData = $this->productsCache[$orderItem['id']];

        if ($productData['instock'] !== "true") {
            throw new DomainException("Product not available: {$orderItem['id']}");
        }

        if ($orderItem['price'] !== $productData['price'] || $orderItem['label'] !== $productData['label']) {
            throw new DomainException(
                "Incorrect price"
            );
        }

        if ($productData['category'] !== $orderItem['category']) {
            throw new DomainException(
                "Invalid category. Expected: {$productData['category']}, provided: {$orderItem['category']}"
            );
        }

        if (!empty($productData['attributes'])) {
            $this->validateProductAttributes($orderItem, $productData);
            $cleanData = $orderItem;
        } else {
            $cleanData = $orderItem;
            $cleanData['selectedOptions'] = null;
        }

        $cleanData['price'] *= $cleanData['count'];
        $cleanData['product_id'] = $cleanData['id'];
        unset($cleanData['id']);
        return $cleanData;
    }

    protected function validateProductAttributes(array $orderItem, array $productData): void
    {
        if (!isset($orderItem['selectedOptions'])) {
            throw new InvalidArgumentException("Selected options required for product: {$orderItem['id']}");
        }

        $selectedOptions = $orderItem['selectedOptions'];

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
                    "Invalid attribute type '{$attributeType}' for product: {$orderItem['id']}"
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

    protected function batchInsertNormalized(array $items): void
    {
        $columns = array_keys($items[0]);
        $chunks = array_chunk($items, $this->batchSize);

        $columnNames = implode(', ', array_map([$this, 'escapeIdentifier'], $columns));
        $rowPlaceholder = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';

        foreach ($chunks as $chunk) {
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
        $this->checkColumnsCount($order['items']);
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