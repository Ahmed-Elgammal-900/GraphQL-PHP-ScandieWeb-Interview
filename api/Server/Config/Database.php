<?php

declare(strict_types=1);

namespace Api\Server\Config;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private function __construct()
    {
        Config::initialize();

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4',
            Config::getHost(),
            Config::getName(),
            Config::getPort()
        );
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_CA => Config::getCA()
        ];

        try {
            $this->connection = new PDO(
                $dsn,
                Config::getUser(),
                Config::getPass(),
                $options
            );
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
