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
    private static ?string $caKey = null;

    public static function initialize(): void
    {
        if (self::$dbHost === null) {
            self::$dbHost = $_ENV['DB_HOST'];
            self::$dbName = $_ENV['DB_NAME'];
            self::$dbUser = $_ENV['DB_USER'];
            self::$dbPass = $_ENV['DB_PASS'];
            self::$dbPort = (int) $_ENV['DB_PORT'];
            self::$caKey = $_ENV['CA_KEY'];
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

    public static function getCA(): string
    {
        $path = "/tmp/ca-cert.pem";
        $decodedKey = base64_decode(self::$caKey);
        file_put_contents($path, $decodedKey);
        return $path;
    }
}