<?php

declare(strict_types=1);

namespace Api\Server\Config;

use Dotenv\Dotenv;

$projectRoot = dirname(__DIR__, 2);

$dotenv = Dotenv::createImmutable($projectRoot);

$dotenv->load();

class Config
{
    private static $dbHost;
    private static $dbName;
    private static $dbUser;
    private static $dbPass;
    private static $dbPort;
    private static $sslCa;

    function __construct()
    {
        self::$dbHost = $_ENV['DB_HOST'];
        self::$dbName = $_ENV['DB_NAME'];
        self::$dbUser = $_ENV['DB_USER'];
        self::$dbPass = $_ENV['DB_PASS'];
        self::$dbPort = $_ENV['DB_PORT'];
        self::$sslCa = base64_decode($_ENV['SSL_CA']);
    }

    public function getDbHost(): string
    {
        return (string) self::$dbHost;
    }

    public function getDbName(): string
    {
        return (string) self::$dbName;
    }

    public function getDbUser(): string
    {
        return (string) self::$dbUser;
    }

    public function getDbPass(): string
    {
        return (string) self::$dbPass;
    }

    public function getDbPort(): int
    {
        return (int) self::$dbPort;
    }

    public function getSslCa(): string
    {
        return (string) self::$sslCa;
    }
}
