<?php

declare(strict_types=1);

namespace Api\Server\Config;

use Dotenv\Dotenv;

$projectRoot = dirname(__DIR__, 3);

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
        return (string) 'ecommerce-scandiweb-ecommerce-database.c.aivencloud.com';
    }

    public function getDbName(): string
    {
        return (string) 'defaultdb';
    }

    public function getDbUser(): string
    {
        return (string) 'avnadmin';
    }

    public function getDbPass(): string
    {
        return (string) 'AVNS_n3L7nRXXuFKhnaQ1Qk4';
    }

    public function getDbPort(): int
    {
        return (int) 28703;
    }

    public function getSslCa(): string
    {
        return (string) 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUVRVENDQXFtZ0F3SUJBZ0lVS01kcGFocHh4d0o4RUFkU3pvMWdmSVhtbjBJd0RRWUpLb1pJaHZjTkFRRU0KQlFBd09qRTRNRFlHQTFVRUF3d3ZNalkwTWpRNVpUUXRNR014TlMwMFl6YzJMV0ZrTjJZdE1qRTJNRGRoTnpSbApabVZsSUZCeWIycGxZM1FnUTBFd0hoY05NalF4TURJMU1UVXpOakl4V2hjTk16UXhNREl6TVRVek5qSXhXakE2Ck1UZ3dOZ1lEVlFRRERDOHlOalF5TkRsbE5DMHdZekUxTFRSak56WXRZV1EzWmkweU1UWXdOMkUzTkdWbVpXVWcKVUhKdmFtVmpkQ0JEUVRDQ0FhSXdEUVlKS29aSWh2Y05BUUVCQlFBRGdnR1BBRENDQVlvQ2dnR0JBTFdSL3dZdQo4YkQzUFlhT0V1bUIxOEUva3kwNElya3l0S1Z6d1BucWUvYUZBWHR1UTZ4TzJ1NE9UWTdBeS96YksvUmpFaGg1Cm82aE1KcTkwcG1WK2h3b2RXOHRuU0E1RXhsMHM5ZlJXOGk5S0xRWHlCK055MkJmY1dDZXd6YTllYkFmYVp1aWcKOGFkd2Z5L29uVGxQVzJkNElxRUJCVGhqNW9TTGxMY3Bwb01sN2pTV1hlNFJoZjJzc280dXdSUGxUUVVZOVIxMwprN0Q1aWIrZnNERVp6MnRsRlk4RVF1Vzd6Y0xabW43a045bHJXNWtwdnVSK1dValFRbzBSRjZwZU9KOWd5U1NBClpnUVhQd2REY296d2NYUGRsVHRWK2drWHpXam9tbDRvSVZPaUdDN2tlWTNWM2thWFF1VkJsWWZxK3p3ODR2S2EKcTFaVlBmWGVkelhOcHd4UVBiK0JqQ0kyZll0VDhHNTBUc3pxczVWZHRlSHFUMFpUbEJjMFNnTEFjcmxxZ2R0VQp2ejMzbHo4T0RodDRQK1ZGUTV2TUhONzEySjAwSkYyMXhMdXppS2ttMzNGV29wbDNUVE1IS3V0OXREUklSdHVUClNPRE1lSXFmYjgyb3dYTkZaL0RvTk83b3dFVnUybUUzSkdRazFOMHNNbzBwN3M1b2F1Q3VIbjArUHdJREFRQUIKb3o4d1BUQWRCZ05WSFE0RUZnUVVFTnVhU1dlNDNyRDdqKzZudHR3aFhZcmdpR0F3RHdZRFZSMFRCQWd3QmdFQgovd0lCQURBTEJnTlZIUThFQkFNQ0FRWXdEUVlKS29aSWh2Y05BUUVNQlFBRGdnR0JBSHUvYU8wbytvcGlRWjFVCjRkTW9XU2dZNGRmTWN6OGl5WWdjZEwybE94VGZvUVM5eUlsSk1hUzZlaGx6RGpoTWkrdWFkVWwySDV2YitzL28KY0U3MlZuRllvN2loRm0vK3l1Vi9rdFRQY1RiZWo2cmFQOUVJTVo4UXNwOG50Q1YyWnc0YnZkQlQyU3MxaGJPbQpNRFpFY2lGQ3E5aDZKRlhQb1NUdWVRSkdlbStVaVEvbHJaV0o1eFpSR3dtSXg2ZVB3TWR2RXVxQmQya2E1VUkzCnNkUEl2eGxCVXJUbHErd3JxV0hNUnJ2M3B5czllR3NxVjFJbzU1dCtZb0ozQ3lUcVFuWHlEb2lGWFNWem4wVEMKQXN0czdwK3NJTjBFanFnb0k1MnR6eE51L3J3N2hiWStyM0lEMzJwSndkNmxJWktiNjVVMWFXK3g1QkxWWUxvNgpCSWxwY1VPa0pwNmVEMzEvZ21EL2lFRTRDa2l5NUxQamZjYWw2YjZ0SUNuUU5ycWJtd2RhVTVNVTVZLzJGbVEvCnR1NTh4RUM0cGVtOEZOMklsaXkwTVlHS0pRRDFsSUZhYmlrRE5BZzNNSkdwbVJ5emJTR1pnUW1sQUUrbzNHbGgKYXFIUFNNT2RLMk5GN0htNFM3TlNOQWFFVHNSRUNOamV5MEVZMjQ0MHZkc0o3bnowRUE9PQotLS0tLUVORCBDRVJUSUZJQ0FURS0tLS0tCg==';
    }
}
