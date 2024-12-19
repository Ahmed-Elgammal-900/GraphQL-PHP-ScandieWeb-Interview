<?php

declare(strict_types=1);

namespace Api\Server\Models;

use Api\Server\Config\Database;

abstract class ProductsModel
{
    protected $db;
    protected $productId;
    protected $connection;

    public function __construct($productId = null)
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
        $this->productId = $productId;
    }

    abstract public function getProduct();
    abstract public function getGallery();
    abstract public function getCurrency();
    abstract public function getPrice();
}

