<?php

declare(strict_types=1);

namespace Api\Server\Models;

use Api\Server\Config\Database;

abstract class AttributesModel
{
    protected Database $database;
    protected string $productId;
    protected ?string $type;
    protected \PDO $connection;

    public function __construct($id, $type = null)
    {
        $this->database = Database::getInstance();
        $this->connection = $this->database->getConnection();
        $this->productId = $id;
        $this->type = $type;
    }

    abstract public function getAttribute();
    abstract public function getAllItems();
}

