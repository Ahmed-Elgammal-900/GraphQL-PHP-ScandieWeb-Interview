<?php

declare(strict_types=1);

namespace Api\Server\Config;

class Config
{
    private static $dbHost;
    private static $dbName;
    private static $dbUser;
    private static $dbPass;
    private static $dbPort;

    function __construct()
    {
        self::$dbHost = $_ENV['DB_HOST'];
        self::$dbName = $_ENV['DB_NAME'];
        self::$dbUser = $_ENV['DB_USER'];
        self::$dbPass = $_ENV['DB_PASS'];
        self::$dbPort = $_ENV['DB_PORT'];
    }

    public function getHost(): string
    {
        return (string) self::$dbHost;
    }

    public function getName(): string
    {
        return (string) self::$dbName;
    }

    public function getUser(): string
    {
        return (string) self::$dbUser;
    }

    public function getPass(): string
    {
        return (string) self::$dbPass;
    }

    public function getPort(): int
    {
        return (int) self::$dbPort;
    }
}
