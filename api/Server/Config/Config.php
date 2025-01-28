<?php

declare(strict_types=1);

namespace Api\Server\Config;

class Config
{
    private static ?string $dbHost = null;
    private static ?string $dbName = null;
    private static ?string $dbUser = null;
    private static ?string $dbPass = null;
    private static ?int $dbPort = null;

    public static function initialize(): void
    {
        if (self::$dbHost === null) {
            self::$dbHost = $_ENV['DB_HOST'];
            self::$dbName = $_ENV['DB_NAME'];
            self::$dbUser = $_ENV['DB_USER'];
            self::$dbPass = $_ENV['DB_PASS'];
            self::$dbPort = (int) $_ENV['DB_PORT'];
        }
    }

    public static function getHost(): string
    {
        return self::$dbHost;  
    }

    public static function getName(): string
    {
        return self::$dbName;  
    }

    public static function getUser(): string
    {
        return self::$dbUser;  
    }

    public static function getPass(): string
    {
        return self::$dbPass;  
    }

    public static function getPort(): int
    {
        return self::$dbPort;  
    }
}