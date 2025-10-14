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

    abstract protected function validateOrderData(array $orderData);
    abstract protected function getProductData(string $productId);
    abstract protected function validateRequiredFields(array $orderItem);
    abstract protected function filterOrderKeys(array $orderItem);
    abstract protected function validateProductAttributes(array $orderData, array $productData);
    abstract protected function escapeIdentifier(string $identifier);
    abstract protected function batchInsertNormalized(array $items);
    abstract public function processOrders(array $order);
}
