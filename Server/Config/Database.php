<?php

declare(strict_types=1);

namespace Server\Config;

use Server\Config\Config;

class Database
{
    private static $instance = null;
    private $connection;
    private function __construct()
    {

        $config = new Config();
        $this->connection = new \mysqli(
        $config->getDbHost(), 
        $config->getDbUser(), 
        $config->getDbPass(), 
        $config->getDbName(), 
        $config->getDbPort()
        );
        $this->connection->ssl_set(null, null, $config->getSslCa(), null, null);
        $this->connection->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
        $this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1000);

        if (
            !$this->connection->real_connect(
                $config->getDbHost(),
                $config->getDbUser(),
                $config->getDbPass(),
                $config->getDbName(),
                $config->getDbPort(),
                null,
                MYSQLI_CLIENT_SSL
            )
        ) {
            die("Connection failed with SSL: " . $this->connection->connect_error);
        }

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

    }

    public static function getInstance(): Database
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }
}
