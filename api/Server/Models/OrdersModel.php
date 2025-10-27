<?php

declare(strict_types=1);

namespace Api\Server\Models;

use Api\Server\Config\Database;

abstract class OrdersModel
{
    protected Database $database;
    protected \PDO $connection;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->connection = $this->database->getConnection();
    }

    protected function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    abstract protected function checkItemStructure(array $items);
    abstract protected function validateOrderData(array $orderData);
    abstract protected function getProductsData(array $orderData);
    abstract protected function validateRequiredFields(array $orderItem);
    abstract protected function validateProductAttributes(array $orderItem, array $productData);
    abstract protected function batchInsertNormalized(array $items);
    abstract public function processOrder(array $order);
}
