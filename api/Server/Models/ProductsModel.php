<?php

declare(strict_types=1);

namespace Api\Server\Models;

use Api\Server\Config\Database;

abstract class ProductsModel
{
    protected Database $database;
    protected ?string $productId;
    protected \PDO $connection;

    public function __construct($productId = null)
    {
        $this->database = Database::getInstance();
        $this->connection = $this->database->getConnection();
        $this->productId = $productId;
    }

    abstract public function getProduct();
    abstract public function getGallery();
    abstract public function getCurrency();
    abstract public function getPrice();
}
