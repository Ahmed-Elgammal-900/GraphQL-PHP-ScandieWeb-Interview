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

    abstract protected function processOrder(array $orderItem);
    abstract protected function validateOrderData(array $orderData);
    abstract protected function validateRequiredFields(array $orderItem);
    abstract protected function filterOrderKeys(array $orderItem);
    abstract protected function escapeIdentifier(string $identifier);
    abstract protected function flattenAssoc(array $orderItem);
    abstract public function processOrders(array $order);

}
