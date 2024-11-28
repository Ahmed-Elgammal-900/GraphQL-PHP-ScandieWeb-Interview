<?php

declare(strict_types=1);

namespace Server\Models;

use Server\Config\Database;

abstract class AttributesModel
{
    protected $db;
    protected $id;
    protected $type;
    protected $connection;

    public function __construct($id, $type = null)
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
        $this->id = $id;
        $this->type = $type;
    }

    abstract public function getAttribute();
    abstract public function getAllItems();
}

