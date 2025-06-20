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

    abstract public function makeOrders($items);
}