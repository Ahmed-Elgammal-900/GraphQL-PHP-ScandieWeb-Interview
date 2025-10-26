<?php

declare(strict_types=1);

namespace Api\Server\Models;

use Api\Server\Config\Database;
use InvalidArgumentException;

abstract class OrdersModel
{
    protected Database $database;
    protected \PDO $connection;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->connection = $this->database->getConnection();
    }

    protected function filterOrderKeys(array $orderItem): void
    {
        foreach ($orderItem as $key => $value) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                throw new InvalidArgumentException("Invalid field name: {$key}");
            }
        }
    }

    protected function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function checkColumnsCount(array $items): void
    {
        $firstItem = $items[0];

        foreach ($items as $index => $item) {

            $missing = array_diff_key($firstItem, $item);
            $extra = array_diff_key($item, $firstItem);

            if ($missing || $extra) {
                throw new InvalidArgumentException(
                    "Item at index {$index} has mismatched keys. " .
                    ($missing ? "Missing: " . implode(', ', array_keys($missing)) . ". " : "") .
                    ($extra ? "Extra: " . implode(', ', array_keys($extra)) : "")
                );
            }
        }
    }

    abstract protected function validateOrderData(array $orderData);
    abstract protected function getProductsData(array $orderData);
    abstract protected function validateRequiredFields(array $orderItem);
    abstract protected function validateProductAttributes(array $orderItem, array $productData);
    abstract protected function batchInsertNormalized(array $items);
    abstract public function processOrder(array $order);
}
