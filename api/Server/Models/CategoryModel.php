<?php

declare(strict_types=1);

namespace Api\Server\Models;

use Api\Server\Config\Database;

abstract class CategoryModel
{
    protected $db;
    protected $connection;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }

    abstract public function getType();
}

